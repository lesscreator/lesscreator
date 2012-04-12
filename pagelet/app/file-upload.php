<?php
$projbase = SYS_ROOT."/app";
$proj = $this->req->proj;
$path = $this->req->path;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $p = preg_replace(array("/\.+/", "/\/+/"), array(".", "/"), "{$projbase}/{$proj}/{$path}/");
    if (!is_writable($p)) {
        header("HTTP/1.1 500"); die("'$p' is not Writable");
    }
    if (!isset($_FILES["attachment"])) {
        header("HTTP/1.1 500"); die("Please select a file");
    }
    
    if ($_FILES["attachment"]["error"] != UPLOAD_ERR_OK) {
        header("HTTP/1.1 500"); die("Can not upload file");
    }
        
    $t  = $_FILES["attachment"]["tmp_name"];
    $p .= '/'.$_FILES["attachment"]["name"];
    $p  = preg_replace(array("/\.+/", "/\/+/"), array(".", "/"), $p);
        
    if (file_exists($p)) {
        header("HTTP/1.1 500"); die("File Exists");
    }
        
    if (!move_uploaded_file($t, $p)) {
        header("HTTP/1.1 500"); die("Can not upload file");
    }
        
    header("HTTP/1.1 200"); die("Saved successfully");
}
header("HTTP/1.1 500"); die("Can not upload file");
?>
