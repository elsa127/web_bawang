<?php

/**
 * Script diagnostik sementara: baca header TIFF secara manual
 * untuk mengetahui struktur file binary TIF.
 * Tidak membutuhkan ekstensi GDAL.
 */
$files = [
    'binary' => __DIR__.'/ml_model/data/XGBOOST_V4_1/Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif',
    'doa' => __DIR__.'/ml_model/data/XGBOOST_V4_1/Candidate_Bawang_DOA_T050_V4_1.tif',
    'prob' => __DIR__.'/ml_model/data/XGBOOST_V4_1/Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif',
];

// Tag ID TIFF yang kita butuhkan
$tagNames = [
    256 => 'ImageWidth',
    257 => 'ImageLength',
    258 => 'BitsPerSample',
    259 => 'Compression',
    262 => 'PhotometricInterpretation',
    273 => 'StripOffsets',
    277 => 'SamplesPerPixel',
    278 => 'RowsPerStrip',
    279 => 'StripByteCounts',
    284 => 'PlanarConfiguration',
    317 => 'Predictor',
    320 => 'ColorMap',
    324 => 'TileOffsets',
    325 => 'TileByteCounts',
    322 => 'TileWidth',
    323 => 'TileLength',
    339 => 'SampleFormat',
    33922 => 'ModelTiepointTag',
    33550 => 'ModelPixelScaleTag',
    34736 => 'GeoDoubleParamsTag',
    34737 => 'GeoAsciiParamsTag',
    34264 => 'ModelTransformationTag',
];

// SampleFormat values
$sampleFormats = [1 => 'UInt', 2 => 'Int', 3 => 'Float', 4 => 'Void'];
// Compression values
$compressions = [1 => 'None', 2 => 'CCITT', 5 => 'LZW', 6 => 'OJPEG', 7 => 'JPEG', 8 => 'Deflate', 32773 => 'PackBits', 32946 => 'Deflate2'];

foreach ($files as $name => $path) {
    echo "\n============================================================\n";
    echo "FILE: $name\n";
    echo "Path: $path\n";
    echo 'Size: '.number_format(filesize($path)).' bytes ('.round(filesize($path) / 1024 / 1024, 2)." MB)\n";
    echo "============================================================\n";

    $h = fopen($path, 'rb');

    // Byte order
    $byteOrder = fread($h, 2);
    $le = ($byteOrder === 'II');
    echo 'Byte order: '.($le ? 'Little-Endian (Intel)' : 'Big-Endian (Motorola)')."\n";

    // Magic
    $magic = $le ? unpack('v', fread($h, 2))[1] : unpack('n', fread($h, 2))[1];
    echo "Magic: $magic (".($magic == 42 ? 'Classic TIFF' : ($magic == 43 ? 'BigTIFF' : 'Unknown')).")\n";

    if ($magic == 43) {
        echo "BigTIFF - offset size 8 bytes\n";
        fclose($h);

        continue;
    }

    // IFD offset
    $ifdOffset = $le ? unpack('V', fread($h, 4))[1] : unpack('N', fread($h, 4))[1];
    echo "IFD offset: $ifdOffset\n";

    fseek($h, $ifdOffset);
    $entryCount = $le ? unpack('v', fread($h, 2))[1] : unpack('n', fread($h, 2))[1];
    echo "IFD entry count: $entryCount\n\n";

    $tags = [];
    for ($i = 0; $i < $entryCount; $i++) {
        $tagData = fread($h, 12);
        if (strlen($tagData) < 12) {
            break;
        }

        $tag = $le ? unpack('v', substr($tagData, 0, 2))[1] : unpack('n', substr($tagData, 0, 2))[1];
        $type = $le ? unpack('v', substr($tagData, 2, 2))[1] : unpack('n', substr($tagData, 2, 2))[1];
        $count = $le ? unpack('V', substr($tagData, 4, 4))[1] : unpack('N', substr($tagData, 4, 4))[1];
        $valueOffset = substr($tagData, 8, 4);

        // Type sizes: 1=BYTE(1), 2=ASCII(1), 3=SHORT(2), 4=LONG(4), 5=RATIONAL(8), 11=FLOAT(4), 12=DOUBLE(8)
        $typeSizes = [1 => 1, 2 => 1, 3 => 2, 4 => 4, 5 => 8, 6 => 1, 7 => 1, 8 => 2, 9 => 4, 10 => 8, 11 => 4, 12 => 8];
        $typeSize = $typeSizes[$type] ?? 1;

        // Baca nilai inline (jika total byte ≤ 4)
        $totalBytes = $typeSize * $count;
        $value = null;
        if ($totalBytes <= 4) {
            if ($type == 3 && $count == 1) {
                $value = $le ? unpack('v', substr($valueOffset, 0, 2))[1] : unpack('n', substr($valueOffset, 0, 2))[1];
            } elseif ($type == 4 && $count == 1) {
                $value = $le ? unpack('V', $valueOffset)[1] : unpack('N', $valueOffset)[1];
            } elseif ($type == 1 && $count == 1) {
                $value = ord($valueOffset[0]);
            } elseif ($type == 3 && $count <= 2) {
                $values = [];
                for ($j = 0; $j < $count; $j++) {
                    $values[] = $le ? unpack('v', substr($valueOffset, $j * 2, 2))[1] : unpack('n', substr($valueOffset, $j * 2, 2))[1];
                }
                $value = implode(', ', $values);
            } else {
                $value = '(inline)';
            }
        } else {
            // Nilai di offset lain
            $offset = $le ? unpack('V', $valueOffset)[1] : unpack('N', $valueOffset)[1];
            $pos = ftell($h);
            fseek($h, $offset);
            if ($type == 3 && $count <= 10) {
                $vals = [];
                for ($j = 0; $j < $count; $j++) {
                    $vals[] = $le ? unpack('v', fread($h, 2))[1] : unpack('n', fread($h, 2))[1];
                }
                $value = implode(', ', $vals);
            } elseif ($type == 4 && $count <= 10) {
                $vals = [];
                for ($j = 0; $j < $count; $j++) {
                    $vals[] = $le ? unpack('V', fread($h, 4))[1] : unpack('N', fread($h, 4))[1];
                }
                $value = implode(', ', $vals);
            } elseif ($type == 12 && $count <= 6) {
                $vals = [];
                for ($j = 0; $j < $count; $j++) {
                    $vals[] = round(unpack('d', fread($h, 8))[1], 8);
                }
                $value = implode(', ', $vals);
            } elseif ($type == 11 && $count <= 10) {
                $vals = [];
                for ($j = 0; $j < $count; $j++) {
                    $vals[] = round(unpack('f', fread($h, 4))[1], 6);
                }
                $value = implode(', ', $vals);
            } else {
                $value = "(offset=$offset, count=$count)";
            }
            fseek($h, $pos);
        }

        $tags[$tag] = $value;

        $tagLabel = $tagNames[$tag] ?? "Tag#$tag";
        $typeNames = [1 => 'BYTE', 2 => 'ASCII', 3 => 'SHORT', 4 => 'LONG', 5 => 'RATIONAL', 11 => 'FLOAT', 12 => 'DOUBLE'];
        $typeName = $typeNames[$type] ?? "Type$type";

        // Terjemahkan nilai khusus
        $display = $value;
        if ($tag == 339 && is_numeric($value)) {
            $display .= ' ('.($sampleFormats[(int) $value] ?? '?').')';
        }
        if ($tag == 259 && is_numeric($value)) {
            $display .= ' ('.($compressions[(int) $value] ?? '?').')';
        }

        echo sprintf("  %-30s [%6s x%d] = %s\n", $tagLabel, $typeName, $count, $display);
    }

    fclose($h);
}
