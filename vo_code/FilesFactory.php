<?php
// www.IQB.hu-berlin.de
// Bărbulescu, Stroescu, Mechtel
// 2018
// license: MIT

class ResourceFile {
    private $isXml = false;
    private $size = 0;
    private $filedate;

    public function __construct($filename, $unixtimestamp, $filesize) {
        $this->name = $filename;
        $this->filedate = date(DATE_ATOM, $unixtimestamp);
        $this->size = $filesize;
        $this->isXml = preg_match("/\.(XML|xml|Xml)$/", $filename) == true;
    }

    public function getFileName() {
        return $this->name;
    }

    public function getFileDateTime() {
        if (isset($this->filedate) && (strlen($this->filedate) > 0)) {
            return strtotime ( $this->filedate );
        } else {
            return 0;
        }
    }

    public function getFileDateTimeString() {
        $filedatevalue = $this->getFileDateTime();
        if ($filedatevalue == 0) {
            return 'n/a';
        } else {
            return strftime('%d.%m.%Y', $filedatevalue);
        }
    }

    public function getFileSize() {
        return $this->size;
    }

    public function getFileSizeString() {
        return FileFactory::filesizeAsString($this->size);
    }

    public function getIsXml() {
        return $this->isXml;
    }
}

// #################################################################################################
class FileFactory {
    public static $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

    // __________________________
    static function readfolder( $startfolder ) {
        $myreturn = [];
        if (file_exists($startfolder)) {
            $mydir = opendir($startfolder);
            while (($entry = readdir($mydir)) !== false) {
                $fullfilename = $startfolder . $entry;
                if (is_file($fullfilename)) {
                    $rs = new ResourceFile($entry, filemtime($fullfilename), filesize($fullfilename));
                    array_push($myreturn, $rs);
                }
            }
        } else {
            echo '##';
        }
        return $myreturn;
    }

    // __________________________
    static function filesizeAsString ( $filesize ) {
        if ($filesize == 0) {
            return '-';
        } else {
            return round($filesize/pow(1024, ($i = floor(log($filesize, 1024)))), 2) . ' ' . FileFactory::$sizes[$i];
        }
    }
}

// #################################################################################################
class XFileFactory {

    // __________________________
    static function getBookletName($workspaceId, $bookletId) {
        $myreturn = '';

        $lookupFolder = '../vo_data/ws_' . $workspaceId . '/Booklet';
        if (file_exists($lookupFolder)) {
            $lookupDir = opendir($lookupFolder);
            if ($lookupDir !== false) {
                require_once('XMLFile.php');

                while (($entry = readdir($lookupDir)) !== false) {
                    $fullfilename = $lookupFolder . '/' . $entry;
                    if (is_file($fullfilename) && (strtoupper(substr($entry, -4)) == '.XML')) {
                        // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                        $xFile = new XMLFile($fullfilename);

                        if ($xFile->isValid()) {
                            if ($xFile->getRoottagName()  == 'Booklet') {
                                $myBookletId = $xFile->getId();
                                if ($myBookletId === $bookletId) {
                                    $myreturn = $xFile->getLabel();
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $myreturn;
    }
}

?>