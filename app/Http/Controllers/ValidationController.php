<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ValidationController extends Controller
{
    private $returnData = [];
    
    /**
     * The standards I have found say that each line in the ads.txt file
     * should have either 3 or 4 fields in them. At least 1 of the examples I 
     * have looked at have had more. I'm setting a minimum of 3 for this reason.
     * 
     * @var Integer
     */
    private $columnMin = 3;
    
    private $status = false;
    
    function index() {
    
        return view('welcome');
    }
    
    /**
     * 
     * @param Request $req
     */
    function post(Request $req) {
        
        $url = $req->all()['url'];
        if(empty($url)) {
            $this->returnData['errors'][] = 'URL is required.';
        } else {
            $this->returnData['url'] = $url;
            $adsTxtFile = file_get_contents('http://' . $url . '/ads.txt');
            $adsTxtFileArray = explode("\n", $adsTxtFile);
            if(!$adsTxtFileArray || !is_array($adsTxtFileArray)) {
                $this->returnData['errors'][] = 'Remote file access failed. Please check your domain and try again.';
            } else {
                $this->validateFile($adsTxtFileArray);
                if($this->status) {
                    $this->returnData['originalFile']  = $adsTxtFileArray;
                    $this->returnData['validationStatus'] = $this->status;
                }
            }
            
            $ValidationModel = new \App\validation_results();
            $ValidationModel->url = $url;
            $ValidationModel->contents = $adsTxtFile;
            $ValidationModel->status = $this->status;
            $ValidationModel->save();
        }
        
        echo json_encode($this->returnData);
        exit; // Ajax requests shouldn't be allowed to continue.
    }
    
    /**
     * While I am new to the ads.txt technology. I have based my validation rules
     * on this URL: https://support.google.com/dfp_premium/answer/7544382?hl=en
     * I also broke out methods for each rule to allow flexibility if/when these
     * rules change.
     * 
     * @param array $contents
     */
    private function validateFile(Array $contents) {

        // Validate file formatting.
        $i=1;
        foreach($contents as $line) {
            $ignore = ['#', ' ', ''];
            if(! in_array(substr($line, 0, 1), $ignore)) {
                
                if(!$this->checkColumnCount($line, $i)) {
                    $this->returnData['errors'][] = 'Ads.txt column count is wrong on line: ' . $i . '.';
                }
                
                if(! $this->directOrReseller($line)) {
                    $this->returnData['errors'][] = 'Invalid field 3 on line: ' . $i . '.';
                }
            }
            $i++;
        }
        $this->setStatus();
    }
    
    private function setStatus() {
        $this->status = ( empty($this->returnData['errors']) ) ? true : false;
    }
    
    /**
     * 
     * @param String $line
     * @param Integer $lineNum
     */
    private function checkColumnCount($line, $lineNum) {
        return ( count(explode(',', $line)) < $this->columnMin ) ? false : true;
    }
    
    private function directOrReseller($line) {
        $field3 = explode(',', $line)[2];
        return ( strtolower(substr(trim($field3), 0, 6)) == 'direct' || strtolower(substr(trim($field3), 0, 8)) == 'reseller' ) ? true : false;
    }
    
}
