<?php

    if(isset($_POST['submit'])){

        if(!isset($_FILES['myfile']) || $_FILES['myfile']['error'] !== UPLOAD_ERR_OK){
            echo "Error";
            exit;
        }

        $src = $_FILES['myfile']['tmp_name'];
        $ext = explode('.', $_FILES['myfile']['name']);
        $ext = strtolower(end($ext));
        $name = time().".".$ext;

        $dir = __DIR__ . '/../asset/uploads/';
        if(!is_dir($dir)){
            mkdir($dir, 0775, true);
        }

        $des = $dir . $name;

        if(move_uploaded_file($src, $des)){
            echo "success";
            echo "<br><a href='../view/upload.php'>Upload another</a>";
            echo " | <a href='../view/home.php'>Home</a>";
        }else{
            echo "Error";
        }
    }else{
        echo "Error";
    }
?>
