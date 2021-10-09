<?php
//parser.php --file example_1.csv --unique-combinations=combination_count.csv
ini_set('memory_limit', '-1');
define('ROOTPATH', __DIR__);

//Check whether the passed arguments count are correct
if ($argc == 4) {
    // the arguments as an array. first argument is always the script name
    $readFileName = $argv[2];
    $argumentLast = explode('=', $argv[3]);
    $generateUniqueCombinationFileName = $argumentLast[1];
    $filePath = ROOTPATH . '/examples/' . $readFileName;
    $uniqueCombinationFilePath = ROOTPATH . '/examples/' . $generateUniqueCombinationFileName;

    //Check whether the file exists on the location
    if (file_exists($filePath)) {
        $row = 1;
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $rowArray = [];
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $mask = '';
                $columnArray = [];
                $rowArray[] = $data;
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    $mask .= "|%-20.20s ";
                    $columnArray[] = $data[$c];
                }
                $mask .= " |\n";
                printf_array($mask, $columnArray);
            }
            fclose($handle);

            //Save unique combination file
            saveUniqueCombinationFile($rowArray, $uniqueCombinationFilePath);
        }

    } else {
        echo "Sorry! file doesn't exists\n";
    }
} else {
    echo "Could not get value of command line option\n";
}

//Function to display data in table format in console
function printf_array($format, $arr)
{
    return call_user_func_array('printf', array_merge((array)$format, $arr));
}


//Function to save unique combination file
function saveUniqueCombinationFile($data, $path)
{
    echo "Wait...Unique combination count file is generating.\n";
    $filename = $path;
    // open csv file for writing
    $f = fopen($filename, 'w');

    if ($f === false) {
        die('Error opening the file ' . $filename);
    }
    $cnt = 0;
    $duplicateArrays = [];
    $prepareArray = [];
    $prepareArrayHeader = [];
    foreach ($data as $row) {
        array_push($row, '0');
        $val = implode('|', $row);
        if ($cnt == 0) {
            unset($row[count($row) - 1]); //Remove the last element
            array_push($row, 'count');
            $newVal = implode('|', $row);
            $tempKey = '"'.$newVal.'"';
            $key = str_replace('"', '', $tempKey);
            $prepareArrayHeader[] = (explode('|', $key));
        } else {
            $prepareArray[] = '"'.$val.'"';
        }
        $cnt++;
    }
    $countsArrayAppearance = array_count_values($prepareArray);
    foreach ($countsArrayAppearance as $key => $count) {
        $key = str_replace('"', '', $key);
        $tempArray = (explode('|', $key));
        unset($tempArray[count($tempArray) - 1]);
        array_push($tempArray, $count);
        $duplicateArrays[] = array_values($tempArray);
    }

    $finalCountArray = array_merge($prepareArrayHeader, $duplicateArrays);

    // write each row at a time to a file
    foreach ($finalCountArray as $row) {
        fputcsv($f, $row);
        $cnt ++;
    }

    // close the file
    fclose($f);

    echo "Unique combination count file is generated.\n";
}
