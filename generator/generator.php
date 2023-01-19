#!/usr/bin/env php
<?php
chdir(__DIR__);
$search = [];
$replace = [];
foreach (getenv() as $k => $v) {
    $search[] = "\$$k";
    $replace[] = '"' . addslashes($v) . '"';
}
$conf = yaml_parse(
    str_replace($search, $replace, file_get_contents(__DIR__ . "/config.yaml"))
);
$c = new \Mosquitto\Client();
if (isset($conf["mqtt"]["user"])) {
    $c->setCredentials($conf["mqtt"]["user"], $conf["mqtt"]["password"]);
}
$c->onMessage(function (\Mosquitto\Message $message) use (&$data, $conf) {
    if (!isset($data[$message->topic])) {
        return;
    }

    switch ($data[$message->topic]["cast"]) {
        case "int":
            $data[$message->topic]["value"] = (int) $message->payload;
            break;
        case "float":
            $data[$message->topic]["value"] = (float) $message->payload;
            break;
        case "bool":
            $data[$message->topic]["value"] = (bool) $message->payload;
            break;
        default:
            echo "error, cast type " .
                $data[$message->topic]["cast"] .
                " unknown\n";
            die(1);
    }
    var_dump($data);
    $data[$message->topic]["changed"] = time();
    $out = file_get_contents(__DIR__ . "/template.json");
    $out = preg_replace_callback(
        "/\"{{(?<name>[^#}]+?)(#(?<modifier>[^}]+))?}}\"/",
        function ($m) use ($data, $conf) {
            $value = $data[$conf["variables"][$m["name"]]["topic"]]["value"];
            $modifier = isset($m["modifier"]) ? $m["modifier"] : "noop";
            switch ($modifier) {
                case "timestamp":
                    $value =
                        $data[$conf["variables"][$m["name"]]["topic"]][
                            "changed"
                        ];
                    break;
                case "noop":
                    break;

                default:
                    echo "error, modifier " . $m["modifier"] . " unknown\n";
                    die(1);
            }
            return json_encode($value);
        },
        $out
    );

    $out = json_encode(
        json_decode($out),
        JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    if (!is_dir("public")) {
        mkdir("public");
    }
    file_put_contents("public/spaceapi.json", $out);
});
$c->connect($conf["mqtt"]["host"]);
$data = [];
foreach ($conf["variables"] as $name => $variable) {
    $data[$variable["topic"]] = [
        "value" => null,
        "cast" => $variable["cast"],
        "name" => $name,
        "changed" => 0,
    ];
    $c->subscribe($variable["topic"], 2);
}
$c->loopForever();

