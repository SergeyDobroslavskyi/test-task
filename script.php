<?php

//Путь до access.log файла
$filePath = "./access.log";

echo json_encode(Output($filePath), JSON_PRETTY_PRINT);

function Output(string $filePath)
{ 
    $logFile = [
        "views" => 0,
        "urls" => 0,
        "traffic" => 0,
        "crawlers" => [
            "Google" => 0,
            "Bing" => 0,
            "Baidu" => 0,
            "Yandex" => 0
        ]
    ];
    $remote_hosts = [];
    $status_codes = [];
    $pattern = '/^([^ ]+) ([^ ]+) ([^ ]+) (\[[^\]]+\]) "(.*) (.*) (.*)" ([0-9\-]+) ([0-9\-]+) "(.*)" "(.*)"$/';

    $fd = fopen($filePath, 'r') or die("не удалось открыть файл");

    while (!feof($fd)) {
        if ($line = trim(fgets($fd))) {
            if (preg_match($pattern, $line, $matches)) {
                list(
                    $line,
                    $remote_host,
                    $logname,
                    $user,
                    $time,
                    $method,
                    $request,
                    $protocol,
                    $status,
                    $bytes,
                    $referer,
                    $user_agent
                ) = $matches;

                if (!array_search($remote_host, $remote_hosts)) {
                    $remote_hosts[] = $remote_host;
                }

                if (!array_key_exists($status, $status_codes)) {
                    $status_codes[$status] = 1;
                } else {
                    $status_codes[$status]++;
                }

                $logFile["views"] = count(file($filePath));
                $logFile["urls"] = count($remote_hosts);
                $logFile["traffic"] += $bytes;
                $logFile["statusCodes"] = $status_codes;

                $bots_pattern = "/bot|google|yandex|bing|baidu/i";
                preg_match($bots_pattern, $user_agent, $bot_result);
                if (!empty($bot_result)) {
                    list($bot_name) = $bot_result;
                    if (!array_key_exists($bot_name, $logFile["crawlers"])) {
                        $logFile["crawlers"][$bot_name] = 1;
                    } else {
                        $logFile["crawlers"][$bot_name]++;
                    }
                }
            }
        }
    }
    return $logFile;
}
return 0;
?>