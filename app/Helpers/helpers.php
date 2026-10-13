<?php

if (!function_exists('settings')) {
    function settings() {
        $settings = cache()->remember('settings', 24*60, function () {
            return \Modules\Setting\Entities\Setting::firstOrFail();
        });

        return $settings;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($value, $format = true) {
        if (!$format) {
            return $value;
        }

        $settings = settings();
        $position = $settings->default_currency_position;
        $symbol = $settings->currency->symbol;
        $decimal_separator = $settings->currency->decimal_separator;
        $thousand_separator = $settings->currency->thousand_separator;
        // Rupiah tidak memakai angka desimal
        $decimals = $settings->currency->code == 'IDR' ? 0 : 2;

        if ($position == 'prefix') {
            $formatted_value = $symbol . number_format((float) $value, $decimals, $decimal_separator, $thousand_separator);
        } else {
            $formatted_value = number_format((float) $value, $decimals, $decimal_separator, $thousand_separator) . $symbol;
        }

        return $formatted_value;
    }
}

if (!function_exists('make_reference_id')) {
    function make_reference_id($prefix, $number) {
        $padded_text = $prefix . '-' . str_pad($number, 5, 0, STR_PAD_LEFT);

        return $padded_text;
    }
}

if (!function_exists('array_merge_numeric_values')) {
    function array_merge_numeric_values() {
        $arrays = func_get_args();
        $merged = array();
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                if (!isset($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] += $value;
                }
            }
        }

        return $merged;
    }
}


if (!function_exists('sql_date_format')) {
    /**
     * Ekspresi SQL untuk memformat kolom tanggal yang berjalan di MySQL maupun SQLite.
     * Format yang didukung: %d, %m, %Y, %y.
     */
    function sql_date_format($column, $format) {
        if (\Illuminate\Support\Facades\DB::getDriverName() == 'sqlite') {
            $parts = preg_split('/(%[dmYy])/', $format, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $sql = array_map(function ($part) use ($column) {
                if ($part == '%y') {
                    return "substr(strftime('%Y', $column), 3, 2)";
                }
                if (in_array($part, ['%d', '%m', '%Y'])) {
                    return "strftime('$part', $column)";
                }
                return "'" . str_replace("'", "''", $part) . "'";
            }, $parts);

            return implode(' || ', $sql);
        }

        return "DATE_FORMAT($column, '$format')";
    }
}
