<?php 
    $toggle_class = "";
    $toggle_class_teacher = "";
    $useTeacher = 0;
    $use = 0;
    $filepath = "assets/files/useUE.txt";
    $filepath_teacher = "assets/files/useTeacher.txt";

    try {
        $use = (int)file_get_contents($filepath, false, null, 0, 1);
        $toggle_class = $use == 1 ? "active" : "";
        
    } catch (Exception $e) {
        
    }
    
    if(isset($_GET["ua"])) {
        if ($use == 1) {
            file_put_contents($filepath, "0 \n Do not modify");
        } else {
            file_put_contents($filepath,"1 \n Do not modify");
        }
        $use = (int)file_get_contents($filepath, false, null, 0, 1);
        $toggle_class = $use == 1 ? "active" : "";
    }

    try {
        $useTeacher = (int)file_get_contents($filepath_teacher, false, null, 0, 1);
        $toggle_class_teacher = $useTeacher == 1 ? "active" : "";
        
    } catch (Exception $e) {
        
    }
    
    if(isset($_GET["teacher"])) {
        if ($useTeacher == 1) {
            file_put_contents($filepath_teacher, "0 \n Do not modify");
        } else {
            file_put_contents($filepath_teacher,"1 \n Do not modify");
        }
        $useTeacher = (int)file_get_contents($filepath_teacher, false, null, 0, 1);
        $toggle_class_teacher = $useTeacher == 1 ? "active" : "";
    }
?>