<?php

/**
 * sentry.io
 */

namespace app\pack\tracker;

class Sentry
{
    /**
     * get_pack('rollbar.sentry','init');
     */
    public static function init()
    {
        $sentry = get_param('sentry');
        if (!$sentry) {
            return;
        }
        \Sentry\init([
            'dsn' => $sentry,
            // Specify a fixed sample rate
            'traces_sample_rate' => 1.0,
            // Set a sampling rate for profiling - this is relative to traces_sample_rate
            'profiles_sample_rate' => 1.0,
       ]);
    }

    public static function info($str, $type = 'info')
    {
        \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($str, $type) {
            $scope->setLevel(\Sentry\Severity::$type());
            if (is_array($str)) {
                $scope->setExtras($str);
                $message = '';
                foreach ($str as $key => $value) {
                    $message .= $key.':'.$value.';';
                }
                \Sentry\captureMessage($message);
            } else {
                \Sentry\captureMessage($str);
            }
        });
    }

    public static function error($str)
    {
        return self::info($str, 'error');
    }

}
