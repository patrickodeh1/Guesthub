<?php

namespace App\Services;

/**
 * Confirms the guest's government ID on upload using only on-device data —
 * no cloud provider and no billing required:
 *
 *  - US state IDs: the PDF417 barcode decoded in the browser (AAMVA).
 *  - Passports / national IDs: the machine-readable zone (MRZ) or printed
 *    labels, OCR'd in the browser with Tesseract.js and sent as raw text.
 *
 * From that it extracts the holder's name and expiry date and checks two
 * things automatically: the name matches the reservation, and the document
 * hasn't expired. Everything else (the actual photo, age, authenticity) is
 * still verified manually by the host. If neither the name nor any dates
 * could be read, the upload is flagged so the guest is asked to retake it.
 */
class IdDocumentScanner
{
    /**
     * @param  string|null  $barcodeText  decoded AAMVA barcode (state IDs)
     * @param  string|null  $expectedName  the reservation guest name to match against
     * @param  string|null  $ocrText  raw OCR text of the photo (passports)
     * @return array{status:string,date_of_birth:?string,expiry_date:?string,age:?int,number:?string,name:?string}
     */
    public function scan(?string $barcodeText = null, ?string $expectedName = null, ?string $ocrText = null): array
    {
        $fields = [
            'date_of_birth' => null,
            'expiry_date' => null,
            'number' => null,
            'name' => null,
        ];

        // The AAMVA barcode is the most reliable source (name/DOB/expiry).
        if ($barcodeText) {
            $barcodeFields = $this->parseAamva($barcodeText);
            if ($barcodeFields) {
                foreach (['date_of_birth', 'expiry_date', 'number', 'name'] as $key) {
                    if (! empty($barcodeFields[$key])) {
                        $fields[$key] = $barcodeFields[$key];
                    }
                }
            }
        }

        // On-device OCR text (MRZ first, then labels) fills in what the
        // barcode didn't provide.
        if ($ocrText && trim($ocrText) !== '') {
            $ocrFields = $this->extractFields($ocrText);
            foreach (['date_of_birth', 'expiry_date', 'number', 'name'] as $key) {
                if (empty($fields[$key]) && ! empty($ocrFields[$key])) {
                    $fields[$key] = $ocrFields[$key];
                }
            }
        }

        return $this->normalize($fields, $expectedName);
    }

    /**
     * @param  array{date_of_birth?:?string,expiry_date?:?string,number?:?string,name?:?string}  $fields
     */
    private function normalize(array $fields, ?string $expectedName): array
    {
        $dob = $fields['date_of_birth'] ?? null;
        $expiry = $fields['expiry_date'] ?? null;
        $number = $fields['number'] ?? null;
        $name = $fields['name'] ?? null;

        $age = $dob ? (int) \Carbon\Carbon::parse($dob)->age : null;

        $result = [
            'status' => 'valid',
            'date_of_birth' => $dob,
            'expiry_date' => $expiry,
            'age' => $age,
            'number' => $number ? mb_substr((string) $number, 0, 64) : null,
            'name' => $name ? mb_substr(trim($name), 0, 120) : null,
        ];

        // Name must match the reservation.
        if ($expectedName && $result['name']) {
            $match = $this->namesMatch($result['name'], $expectedName);
            if ($match === false) {
                $result['status'] = 'name_mismatch';

                return $result;
            }
        }

        // Document must not have expired.
        if ($expiry && \Carbon\Carbon::parse($expiry)->endOfDay()->isPast()) {
            $result['status'] = 'expired';

            return $result;
        }

        // Under 18 is recorded and surfaced to the host, but not auto-rejected
        // here — the host verifies it manually.
        if ($age !== null && $age < 18) {
            $result['status'] = 'underage';

            return $result;
        }

        // All three fields must have actually been read (and, for MRZ sources,
        // check-digit verified — see parseMrz) before a submission is allowed
        // through as "valid". A *partial* read used to slip through silently:
        // e.g. expiry read but name didn't, which skipped the name-match check
        // entirely (it only runs `if ($expectedName && $result['name'])`) and
        // let an unverified name reach the approval queue looking identical to
        // a fully verified one. Requiring all three closes that gap — anything
        // incomplete is routed back to the guest for a retake instead.
        if (! $name || ! $dob || ! $expiry) {
            $result['status'] = 'unreadable';

            return $result;
        }

        return $result;
    }

    /**
     * Compare an ID holder's name with the reservation name. Order-insensitive
     * and tolerant of middle names: matches when the smaller name's tokens all
     * appear in the larger one (so "James Smith" matches "SMITH JOHN JAMES",
     * but "Jim Smith" does not match "SMITH JAMES").
     */
    public function namesMatch(string $idName, string $expectedName): ?bool
    {
        $tokens = function (string $value): array {
            $parts = preg_split('/[^A-Za-z]+/', strtoupper($value)) ?: [];

            return array_values(array_filter($parts, fn ($part) => strlen($part) >= 2));
        };

        $id = $tokens($idName);
        $expected = $tokens($expectedName);

        if (! $id || ! $expected) {
            return null;
        }

        $overlap = count(array_intersect($id, $expected));

        // Exact matching alone only survives a clean OCR read. In practice a
        // token is often one character off (e.g. a misread MRZ filler
        // character glued onto a name, giving "PATRICKS" instead of
        // "PATRICK") -- close enough that a human reviewing the photo would
        // call it a match, but exact string equality won't. Fuzzy-match any
        // expected token that didn't already match exactly, tolerating a
        // small edit distance relative to token length.
        if ($overlap < count($expected)) {
            foreach ($expected as $expToken) {
                if (in_array($expToken, $id, true)) {
                    continue;
                }
                foreach ($id as $idToken) {
                    $maxLen = max(strlen($expToken), strlen($idToken));
                    $tolerance = $maxLen <= 4 ? 1 : (int) floor($maxLen * 0.2);
                    if (levenshtein($expToken, $idToken) <= max(1, $tolerance)) {
                        $overlap++;
                        break;
                    }
                }
            }
        }

        $required = min(2, count($id), count($expected));

        return $overlap >= max(1, $required);
    }

    /**
     * Parse AAMVA fields from a decoded driver's-licence barcode:
     * DBB = date of birth, DBA = expiry, DAQ = document number,
     * DAA = full name, DCS/DAC = last/first name.
     */
    private function parseAamva(string $text): ?array
    {
        $map = [];
        foreach (preg_split('/[\x1e\r\n]+/', $text) ?: [] as $part) {
            if (preg_match('/^([A-Z]{3})(.*)$/s', trim($part), $m)) {
                $map[$m[1]] = trim($m[2]);
            }
        }

        // Prefer the separator-delimited elements, but fall back to the
        // fixed-width patterns when the decoder concatenated everything.
        $dobValue = $map['DBB'] ?? (preg_match('/DBB(\d{8})/', $text, $m) ? $m[1] : null);
        $expiryValue = $map['DBA'] ?? (preg_match('/DBA(\d{8})/', $text, $m) ? $m[1] : null);
        $number = $map['DAQ'] ?? (preg_match('/DAQ([A-Z0-9]{4,20})/i', $text, $m) ? strtoupper($m[1]) : null);

        $dob = $dobValue ? $this->aamvaDate($dobValue) : null;
        $expiry = $expiryValue ? $this->aamvaDate($expiryValue) : null;

        $name = null;
        if (isset($map['DAA']) && $map['DAA'] !== '') {
            $name = str_replace([',', '@'], ' ', $map['DAA']);
        } elseif (isset($map['DCS']) || isset($map['DAC'])) {
            $name = trim(($map['DAC'] ?? '').' '.($map['DCS'] ?? ''));
        }
        $name = $name ? preg_replace('/\s+/', ' ', trim($name)) : null;

        if (! $dob && ! $expiry) {
            return null;
        }

        return ['date_of_birth' => $dob, 'expiry_date' => $expiry, 'number' => $number ?: null, 'name' => $name ?: null];
    }

    private function aamvaDate(string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 8) {
            return null;
        }

        // AAMVA uses MMDDCCYY.
        $mm = (int) substr($digits, 0, 2);
        $dd = (int) substr($digits, 2, 2);
        $yyyy = (int) substr($digits, 4, 4);

        if ($yyyy >= 1900 && checkdate($mm, $dd, $yyyy)) {
            return sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd);
        }

        // Some encoders emit YYYYMMDD instead.
        $yyyy = (int) substr($digits, 0, 4);
        $mm = (int) substr($digits, 4, 2);
        $dd = (int) substr($digits, 6, 2);

        if ($yyyy >= 1900 && checkdate($mm, $dd, $yyyy)) {
            return sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd);
        }

        return null;
    }

    /**
     * @return array{date_of_birth:?string,expiry_date:?string,number:?string,name:?string,mrz:bool}
     */
    private function extractFields(string $text): array
    {
        // OCR can introduce spaces inside the MRZ band; strip them per line so
        // the fixed-width layout lines up.
        $lines = array_map(
            fn ($line) => preg_replace('/\s+/', '', $line),
            preg_split('/\r\n|\r|\n/', $text) ?: []
        );

        $mrz = $this->parseMrz($lines);
        if ($mrz && ($mrz['date_of_birth'] || $mrz['expiry_date'])) {
            return $mrz + ['mrz' => true];
        }

        // Tesseract often mangles the fixed-width MRZ (wrong char counts,
        // spaces for "<"), so also try a signature-based match: DOB + check
        // digit + sex + expiry is what actually distinguishes the data line.
        $mrz = $this->parseMrzRegex($text);
        if ($mrz && ($mrz['date_of_birth'] || $mrz['expiry_date'])) {
            return $mrz + ['mrz' => true];
        }

        return $this->parseLabelled($text) + ['mrz' => false];
    }

    /**
     * Signature-based MRZ fallback for noisy OCR: finds DOB(YYMMDD) + check +
     * sex + expiry(YYMMDD) anywhere in the text, plus the holder name from any
     * line containing "<<".
     */
    private function parseMrzRegex(string $text): ?array
    {
        // Strip all whitespace so the DOB + check + sex + expiry signature
        // lines up even when Tesseract inserted spaces into the MRZ.
        $clean = strtoupper(preg_replace('/\s+/', '', $text));

        if (! preg_match('/(\d{6})\d([MF<])(\d{6})/', $clean, $m)) {
            return null;
        }

        $dob = $this->mrzDate($m[1], false);
        $expiry = $this->mrzDate($m[3], true);

        if (! $dob && ! $expiry) {
            return null;
        }

        return [
            'date_of_birth' => $dob,
            'expiry_date' => $expiry,
            'number' => null,
            'name' => $this->extractMrzName($text),
        ];
    }

    /**
     * Picks the best "<<"-containing line as the name, rather than the first
     * one. As of the multi-pass OCR change (tight crop + wide crop +
     * full-page joined into one text blob), several candidate lines can
     * exist — a noisy line from the wide-crop or full-page pass can land
     * before the clean tight-crop line and win by default under a
     * first-match strategy. Score instead: reject lines with digits (a
     * genuine MRZ name line is only letters and "<"), then prefer the
     * highest ratio of clean [A-Z<] characters and the closest length match
     * to a standard MRZ line (30/36/44).
     */
    private function extractMrzName(string $text): ?string
    {
        $best = null;
        $bestScore = -INF;

        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = strtoupper(trim($line));
            if ($line === '' || strpos($line, '<<') === false || preg_match('/\d/', $line)) {
                continue;
            }

            $name = $this->mrzName($line);
            if (! $name) {
                continue;
            }

            $len = strlen($line);
            $cleanRatio = strlen(preg_replace('/[^A-Z<]/', '', $line)) / max($len, 1);
            $lengthFit = -min(abs(30 - $len), abs(36 - $len), abs(44 - $len));
            $score = ($cleanRatio * 100) + $lengthFit;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $name;
            }
        }

        return $best;
    }

    /**
     * Parse a machine-readable zone (TD1 3x30, TD2 2x36, TD3 2x44) including
     * the holder's name. DOB and expiry are only trusted when their ICAO
     * check digit actually verifies (see mrzDateChecked) -- OCR misreads a
     * character just as often inside a date as anywhere else, and a wrong
     * DOB/expiry that happens to parse is worse than one we flag as unread,
     * since a wrong-but-plausible date would sail through the age/expiry
     * checks instead of forcing a retake.
     *
     * Tries the mustelaitatsi/mrzparser package first (maintained, tested
     * ICAO check-digit validation) and only falls back to the hand-rolled
     * parser below if the package isn't installed yet or throws on this
     * particular read -- run `composer update` to pull it in.
     */
    private function parseMrz(array $lines): ?array
    {
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            // TD1: three consecutive 30-char lines; name is the 3rd, data 2nd.
            if (strlen($line) === 30 && ($i + 2) < $count
                && strlen($lines[$i + 1]) === 30 && strlen($lines[$i + 2]) === 30) {
                $viaLibrary = $this->parseMrzViaLibrary($line.$lines[$i + 1].$lines[$i + 2]);
                if ($viaLibrary) {
                    return $viaLibrary;
                }

                $data = $lines[$i + 1];

                return [
                    'date_of_birth' => $this->mrzDateChecked($data, 0, 6, false),
                    'expiry_date' => $this->mrzDateChecked($data, 8, 6, true),
                    'number' => $this->mrzNumber(substr($lines[$i], 5, 9)),
                    'name' => $this->mrzName(substr($lines[$i + 2], 0, 30)),
                ];
            }

            // TD2: two consecutive 36-char lines; name is the 1st, data 2nd.
            if (strlen($line) === 36 && ($i + 1) < $count && strlen($lines[$i + 1]) === 36) {
                $viaLibrary = $this->parseMrzViaLibrary($line.$lines[$i + 1]);
                if ($viaLibrary) {
                    return $viaLibrary;
                }

                $data = $lines[$i + 1];

                return [
                    'date_of_birth' => $this->mrzDateChecked($data, 13, 6, false),
                    'expiry_date' => $this->mrzDateChecked($data, 21, 6, true),
                    'number' => $this->mrzNumber(substr($data, 0, 9)),
                    'name' => $this->mrzName(substr($line, 5, 31)),
                ];
            }

            // TD3: two consecutive 44-char lines; name is the 1st, data 2nd.
            if (strlen($line) === 44 && ($i + 1) < $count && strlen($lines[$i + 1]) === 44) {
                $viaLibrary = $this->parseMrzViaLibrary($line.$lines[$i + 1]);
                if ($viaLibrary) {
                    return $viaLibrary;
                }

                $data = $lines[$i + 1];

                return [
                    'date_of_birth' => $this->mrzDateChecked($data, 13, 6, false),
                    'expiry_date' => $this->mrzDateChecked($data, 21, 6, true),
                    'number' => $this->mrzNumber(substr($data, 0, 9)),
                    'name' => $this->mrzName(substr($line, 5, 39)),
                ];
            }
        }

        return null;
    }

    /**
     * Delegate MRZ parsing to mustelaitatsi/mrzparser when it's installed.
     * Only DOB/expiry that the library's own check-digit validation marks
     * `isValid` are trusted -- same "don't guess, flag as unreadable
     * instead" principle as mrzDateChecked() below, just backed by a
     * maintained implementation instead of our own.
     */
    private function parseMrzViaLibrary(string $joinedLines): ?array
    {
        if (! class_exists(\MustelaItatsi\MrzParser\Facades\ParserFacade::class)) {
            return null;
        }

        try {
            $doc = \MustelaItatsi\MrzParser\Facades\ParserFacade::parseMrz($joinedLines);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $doc) {
            return null;
        }

        $checks = method_exists($doc, 'getCheckDigits') ? $doc->getCheckDigits() : [];
        $dobValid = $checks['dateOfBirth']['isValid'] ?? false;
        $expiryValid = $checks['dateOfExpiry']['isValid'] ?? false;

        $surname = method_exists($doc, 'getPrimaryIdentifier') ? $doc->getPrimaryIdentifier() : null;
        $given = method_exists($doc, 'getSecondaryIdentifier') ? $doc->getSecondaryIdentifier() : null;
        $name = trim(($surname ?? '').' '.($given ?? ''));

        return [
            'date_of_birth' => $dobValid && method_exists($doc, 'getDateOfBirthWithEstimatedEpoch')
                ? $doc->getDateOfBirthWithEstimatedEpoch() : null,
            'expiry_date' => $expiryValid && method_exists($doc, 'getDateOfExpiryWithEstimatedEpoch')
                ? $doc->getDateOfExpiryWithEstimatedEpoch() : null,
            'number' => method_exists($doc, 'getDocumentNumber') ? $this->mrzNumber($doc->getDocumentNumber()) : null,
            'name' => $name !== '' ? $name : null,
        ];
    }

    /**
     * Extract a YYMMDD date field at a known offset and only return it if the
     * single check digit immediately following it (per ICAO Doc 9303) is
     * correct. Returns null — not a best-effort guess — on mismatch, so a
     * bad read surfaces as "unreadable" rather than as a wrong date.
     */
    private function mrzDateChecked(string $data, int $offset, int $length, bool $expiry): ?string
    {
        $raw = substr($data, $offset, $length);
        $check = substr($data, $offset + $length, 1);

        if (strlen($raw) !== $length || $check === '' || $this->mrzCheckDigit($raw) !== $check) {
            return null;
        }

        return $this->mrzDate($raw, $expiry);
    }

    /**
     * ICAO Doc 9303 check digit: weights 7,3,1 repeating over each character,
     * digits count as themselves, A-Z as 10-35, '<' (fill) as 0. Sum mod 10.
     */
    private function mrzCheckDigit(string $data): string
    {
        $weights = [7, 3, 1];
        $sum = 0;

        foreach (str_split(strtoupper($data)) as $i => $char) {
            if ($char >= '0' && $char <= '9') {
                $value = (int) $char;
            } elseif ($char >= 'A' && $char <= 'Z') {
                $value = ord($char) - ord('A') + 10;
            } else {
                $value = 0; // '<' and anything unrecognized
            }
            $sum += $value * $weights[$i % 3];
        }

        return (string) ($sum % 10);
    }

    private function mrzName(string $raw): ?string
    {
        $raw = strtoupper(trim($raw));
        if ($raw === '') {
            return null;
        }

        // Strip a leading document-type + issuing-country prefix (fixed
        // ICAO width of 5 chars, e.g. "P<USA"). Positional, not
        // pattern-matched on a literal "<" -- OCR often misreads that
        // separator as a letter (e.g. "P<NGA" -> "PCNGA"), which used to
        // leave the misread character glued onto the front of the name.
        if (strlen($raw) >= 20 && preg_match('/^[A-Z][A-Z0-9<]{4}/', $raw)) {
            $raw = substr($raw, 5);
        }

        // Strip trailing filler padding: a run of 3+ identical characters
        // at the end of the line. Normally "<" repeated, but a misread
        // filler character reads as a *consistent* wrong letter (e.g.
        // "SSSS"), not literal "<"s, so match by repetition rather than by
        // character.
        $raw = preg_replace('/(.)\1{2,}$/', '', $raw);

        // Split surname from given name(s) on the first run of 2+ identical
        // characters (normally "<<"), again by repetition rather than
        // requiring a literal "<<", for the same reason.
        $parts = preg_split('/(.)\1{1,}/', $raw, 2);
        $name = count($parts) === 2
            ? trim(str_replace('<', ' ', $parts[0])).' '.trim(str_replace('<', ' ', $parts[1]))
            : str_replace('<', ' ', $raw);

        $name = trim(preg_replace('/\s+/', ' ', $name));

        return $name !== '' ? $name : null;
    }

    private function mrzNumber(string $raw): ?string
    {
        $value = trim(str_replace('<', '', $raw));

        return $value !== '' ? $value : null;
    }

    /**
     * MRZ YYMMDD to Y-m-d. Expiry dates are always 20YY; birth years over the
     * current two-digit year are assumed to be 19YY.
     */
    private function mrzDate(string $yymmdd, bool $expiry): ?string
    {
        if (! preg_match('/^(\d{2})(\d{2})(\d{2})$/', $yymmdd, $m)) {
            return null;
        }

        $yy = (int) $m[1];
        $mm = (int) $m[2];
        $dd = (int) $m[3];

        if (! checkdate($mm, $dd, 2000)) {
            return null;
        }

        $currentYY = (int) date('y');
        $year = $expiry ? 2000 + $yy : ($yy > $currentYY ? 1900 + $yy : 2000 + $yy);

        return sprintf('%04d-%02d-%02d', $year, $mm, $dd);
    }

    /**
     * Fallback for documents with printed labels (most US driver's licences).
     */
    private function parseLabelled(string $text): array
    {
        $dob = null;
        $expiry = null;
        $number = null;
        $last = null;
        $first = null;

        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $trimmed = trim($line);
            $upper = strtoupper($trimmed);

            if (! $dob && preg_match('/(?:DATE\s*OF\s*BIRTH|BIRTH\s*DATE|\bDOB\b|\bBIRTH\b)/', $upper)) {
                $dob = $this->findDate($line);
            }

            if (! $expiry && preg_match('/(?:EXPIRATION|\bEXPIRES?\b|\bEXP\b|VALID\s*THRU|VALID\s*UNTIL)/', $upper)) {
                $expiry = $this->findDate($line);
            }

            if (! $number && preg_match('/(?:DOC(?:UMENT)?\s*(?:NO|NUMBER)|\bDL\b|\bID\s*NO\b|\bLIC(?:ENCE)?\s*NO\b)/', $upper)) {
                if (preg_match('/[A-Z0-9]{5,20}/', str_replace(' ', '', $upper), $m)) {
                    $number = $m[0];
                }
            }

            if (! $last && preg_match('/^(?:LAST\s*NAME|SURNAME|LN)\b[\s:]*([A-Z][A-Z\'\-\s]{1,40})$/i', $trimmed, $m)) {
                $last = trim($m[1]);
            }

            if (! $first && preg_match('/^(?:FIRST\s*NAME|GIVEN\s*NAMES?|FN)\b[\s:]*([A-Z][A-Z\'\-\s]{1,40})$/i', $trimmed, $m)) {
                $first = trim($m[1]);
            }
        }

        $name = trim(($first ?? '').' '.($last ?? ''));

        return [
            'date_of_birth' => $dob,
            'expiry_date' => $expiry,
            'number' => $number,
            'name' => $name !== '' ? preg_replace('/\s+/', ' ', $name) : null,
        ];
    }

    private function findDate(string $line): ?string
    {
        // ISO YYYY-MM-DD (or with . /)
        if (preg_match('/(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})/', $line, $m)
            && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // US MM/DD/YYYY (fall back to DD/MM/YYYY if MM/DD is invalid).
        if (preg_match('#(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})#', $line, $m)) {
            $a = (int) $m[1];
            $b = (int) $m[2];
            $year = (int) $m[3];

            if (checkdate($a, $b, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $a, $b);
            }
            if (checkdate($b, $a, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $b, $a);
            }
        }

        return null;
    }
}
