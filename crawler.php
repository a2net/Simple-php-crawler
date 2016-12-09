class simpleCrawler{
    public $values;
    public $dom;
    public $finder;
    public $elementsVal;
    public $DomXPath;
    public static $_ERROR = array();
    function __construct($url, $specificField = ''){
        if(!empty($url)){
            $this->crawl_page($url, $specificField);
        }
    }
    function crawl_page($url, $specificField){
        $this->prepareDomElement($url);
        if(!$this->dom){
            return;
        } else {
            $this->prepareDomXPath($this->dom);
        }

        if($specificField != ''){
            if(method_exists($this, $specificField)){
                $this->$specificField();
            }
            return;
        }

        $this->elementsVal['recievedUrl'] = $url;
        $this->getTitle();
        $this->getImage();
        $this->getSupplier();
        $this->getGrade();
        $this->getTags();
        $this->getContentDesc();
    }
    public function clearArray($array){
        $tempArray = array();
        $cleanVal = '';
        if(is_array($array) && count($array) > 0){
            foreach($array as $val){
                $cleanVal = preg_replace('/\s{2,}/', '', $val);
                if($cleanVal) {
                    $tempArray[] = utf8_encode(htmlentities($cleanVal, ENT_QUOTES, "UTF-8"));
                }
            }
        }
        return $tempArray;
    }
    public function getTitle(){
        $title = $this->dom->getElementsByTagName('title');
        foreach($title as $key){
            $title = $key;
        }
        $title = explode('|',$title->nodeValue);
        $this->elementsVal['title'] = utf8_encode(htmlentities(trim($title[0]), ENT_QUOTES, "UTF-8"));
    }
    public function getImage(){
        $mainImage = $this->dom->getElementById('ctl00_MainContent_longListGridView_ctl02_titleImage');
        $this->elementsVal['featuredImage'] = $mainImage->getAttribute('src');
    }
    public function getSupplier(){
        $supplier = $this->dom->getElementById('ctl00_MainContent_longListGridView_ctl02_linkWebSiteLead');
        $this->elementsVal['supplierLink'] = utf8_encode(htmlentities($supplier->getAttribute('href'), ENT_QUOTES, "UTF-8"));
        $this->elementsVal['supplierName'] =  utf8_encode(htmlentities($supplier->textContent, ENT_QUOTES, "UTF-8"));
    }
    public function getGrade(){
        $grade = $this->DomXPath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'sourceGrade')]");
        foreach($grade as $key){
            $grade = $key;
        }
        $this->elementsVal['grade'] = $this->clearArray( explode( PHP_EOL,$grade->textContent) );
    }
    public function getTags(){
        $tags = $this->DomXPath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'cloud')]");
        foreach($tags as $key){
            $tags = $key;
        }
         $this->elementsVal['tags'] = $this->clearArray( explode( PHP_EOL,$tags->textContent) );
    }
    public function getContentDesc(){
        $postDesc = $this->dom->getElementById('ctl00_MainContent_longListGridView_ctl02_descLabel');
        $this->elementsVal['postDesc'] = utf8_encode(htmlentities($postDesc->nodeValue, ENT_QUOTES, "UTF-8"));
    }
    public function prepareDomElement($url){
        $htmlString = file_get_contents($url);
        $dom = new DOMDocument('1.0');
        @$dom->loadHTML(mb_convert_encoding($htmlString, 'HTML-ENTITIES', 'UTF-8'));
        if($dom){
            $this->dom = $dom;
            return;    
        }
        self::$_ERROR[] = 'There were a problem getting DOM Element';
        return false;
    }
    public function prepareDomXPath($dom){
        $this->DomXPath = new DomXPath($dom);
    }
}
