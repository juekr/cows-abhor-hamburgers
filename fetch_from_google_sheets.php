<?php
require_once 'vendor/autoload.php';
use juekr\GoogleSheetsAPI\GoogleSheetsAPI;
use Dotenv\Dotenv;
use CliArgs\CliArgs;

class Fetcher 
{
    private $_params, $_env;
    private $sheet_content = [];
    private $app_name = 'Cards against Humanity – sheets to CSV converter';
    private $unfiltered_white, $unfiltered_black;
    private $filtered_white, $filtered_black;
    private const NECESSARY = [  ];

    function __construct()
    {
        $this->_env = $this->setup_env();
        $this->_params = $this->initialize_cli_and_env_params();
        foreach (self::NECESSARY as $n):
            if ($this->get_option($n, false) == false) die("Option $n is missing.");
        endforeach;
        $this->initialize_sheets_api();
        $this->reload_filtereds();
    }

    private function initialize_sheets_api() 
    {
        $_ENV["RANGE"] = $this->get_option("RANGE", "A:X");
        $g = new GoogleSheetsAPI(__DIR__, $this->app_name);
        $this->sheet_content = $g->list_results();
        $this->unfiltered_black = $this->get_column($this->get_option("BLACK_COLUMN_NAME"));
        $this->unfiltered_white = $this->get_column($this->get_option("WHITE_COLUMN_NAME"));
    }

    private function reload_filtereds() 
    {
        $this->filtered_black = $this->filter($this->unfiltered_black, $this->get_column($this->get_option("BLACK_INCLUDE_NAME")));
        $this->filtered_white = $this->filter($this->unfiltered_white, $this->get_column($this->get_option("WHITE_INCLUDE_NAME")));
    }

    private function initialize_cli_and_env_params()
    {
        $config  = array(
            "csv_filename" => [
                "alias" => "o",
                "help" => "Output (csv) filename (default: ./data/cards.csv)",
                "default" => __DIR__."/data/cards.csv"
            ],
            "range" => [ 
                "alias" => "r",
                "help" => "Column range in format 'A:Z'",
                "default" => "A:Z"
            ], 
            "white_column_name" => [ 
                "alias" => "w",
                "help" => "Name of the column with your WHITE cards",
                "default" => "White"
            ],
            "white_include_name" => [ 
                "alias" => "y",
                "help" => "(optional) WHITE cards: Name of a column indicating whether to include a row in the result set or not – checks for presence of a string (include) or empty (exclude)"
            ],
            "black_column_name" => [ 
                "alias" => "b",
                "help" => "Name of the column with your BLACK cards",
                "default" => "Black"
            ],
            "black_include_name" => [ 
                "alias" => "z",
                "help" => "(optional) BLACK cards: Name of a column indicating whether to include a row in the result set or not – checks for presence of a string (include) or empty (exclude)"
            ],
            "env_file" => [
                "alias" => "e",
                "default" => __DIR__."/.env",
                "help" => "specify an .env file for Google apps access"
            ]
        );
        $CliArgs = new CliArgs($config);
        $return = [];
        foreach ($config as $idx=>$c):
            $return[strtoupper($idx)] = $CliArgs->getArg(strtolower($idx));
            if (isset($c["alias"]))  $return[strtoupper($c["alias"])] = $CliArgs->getArg(strtolower($idx));
        endforeach;
        return $return; 
    }

    private function setup_env()
    {
        $env_file = file_exists($this->get_option("ENV_FILE")) ? $this->get_option("ENV_FILE") : __DIR__.PATH_SEPARATOR.$this->get_option("ENV_FILE");
        if (!file_exists($env_file)) return [];
        $path_parts = explode(PATH_SEPARATOR, $env_file);
        $path = implode(PATH_SEPARATOR, array_slice($path_parts,0, count($path_parts)-1));
        if (empty(trim($path))) $path = __DIR__;
        $file_name = $path_parts[count($path_parts)-1];
        $dotenv = Dotenv::createImmutable($path, $file_name);
        $dotenv->load();
        $dotenv->required([
            'SECRET', 'SHEETS_ID', "SHEET_NAME"
        ]);
        if (!file_exists($_ENV["SECRET"])) die('No secret provided');
        if (empty($_ENV["SHEETS_ID"])) die('No sheet id provided');
        if (empty($_ENV["SHEET_NAME"])) die('No sheet name provided');
        return $_ENV;
    }

    public function write($file_name) 
    {
        $output = fopen($file_name, 'w' );
        $this->write_csv_line_in_utf_16( $output, ["white", "black"] );
        while (count($this->filtered_white) > 0 || count($this->filtered_black) > 0):
            $white = array_shift($this->filtered_white);
            $black = array_shift($this->filtered_black);
            $this->write_csv_line_in_utf_16($output, array($white, $black));
        endwhile;
        fclose($output);
        $this->reload_filtereds();
    }

    private function write_csv_line_in_utf_16($pointer, $arr) {
        $line = "\"".implode("\",\"", $arr)."\"\n";
        fputs($pointer, mb_convert_encoding($line, "UTF-16"));
    }

    private function filter($result_column, $filter_column) 
    {
        $result = array(); // = array("white" => [], "black" => []);
        foreach ($result_column as $idx => $card):
            if (count($filter_column) == 0 || !empty($filter_column[$idx])) $result[] = str_replace("_", "________", $card);
        endforeach;
        return $result;
    }

    private function get_column($key) 
    {
        return array_filter(array_column($this->sheet_content, $key));
    }

    public function get_option($key, $default = null) 
    {
        if (empty($this->_params)) $this->_params = $this->initialize_cli_and_env_params();
        if (!empty($this->_params[strtoupper($key)])) return $this->_params[strtoupper($key)];
    
        return $default;
    }
}

$F = new Fetcher();
if ($F->get_option("CSV_FILENAME", false) != false) $F->write($F->get_option("CSV_FILENAME"));
?>