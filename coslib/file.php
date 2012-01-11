<?php

class file {
    public static function getFileList ($dir, $options = null) {
        return get_file_list($dir, $options);
    }
    
    public static function getFileListRecursive ($start_dir, $pattern = null) {
        return get_file_list_recursive($start_dir, $pattern);
    }
    
    public static function getExtension ($filename) {
        return $ext = substr($filename, strrpos($filename, '.') + 1);
    }

    public static function getMime($path) {
        $result = false;
        if (is_file($path) === true) {
            if (function_exists('finfo_open') === true) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (is_resource($finfo) === true) {
                    $result = finfo_file($finfo, $path);
                }
                finfo_close($finfo);
            } else if (function_exists('mime_content_type') === true) {
                $result = preg_replace('~^(.+);.*$~', '$1', mime_content_type($path));
            } else if (function_exists('exif_imagetype') === true) {
                $result = image_type_to_mime_type(exif_imagetype($path));
            }
        }
        return $result;
    }
}

/**
 * function for getting a file list of a directory (. and .. will not be
 * collected)
 *
 * @param   string  the path to the directory where we want to create a filelist
 * @param   array   if <code>$options['dir_only']</code> isset only return directories.
 *                  if <code>$options['search']</code> isset then only files containing
 *                  search string will be returned
 * @return  array   entries of all files <code>array (0 => 'file.txt', 1 => 'test.php');</code>
 */
function get_file_list($dir, $options = null){
    if (!file_exists($dir)){
        return false;
    }
    $d = dir($dir);
    $entries = array();
    while (false !== ($entry = $d->read())) {
        if ($entry == '..') continue;
        if ($entry == '.') continue;
        if (isset($options['dir_only'])){
            if (is_dir($dir . "/$entry")){
                if (isset($options['search'])){
                    if (strstr($entry, $options['search'])){
                       $entries[] = $entry;
                    }
                } else {
                    $entries[] = $entry;
                }
            }
        } else {
            $entries[] = $entry;
        }
    }
    $d->close();
    return $entries;
}

/**
 * function for getting a file list recursive
 * @param string $start_dir the directory where we start
 * @param string $pattern a given fnmatch() pattern
 * return array $ary an array with the files found. 
 */
function get_file_list_recursive($start_dir, $pattern = null) {

    $files = array();
    if (is_dir($start_dir)) {
        $fh = opendir($start_dir);
        while (($file = readdir($fh)) !== false) {
            // skip hidden files and dirs and recursing if necessary
            if (strpos($file, '.')=== 0) continue;
            
            $filepath = $start_dir . '/' . $file;
            if ( is_dir($filepath) ) {
                $files = array_merge($files, file::getFileListRecursive($filepath, $pattern));
            } else {
                if (isset($pattern)) {
                    if (fnmatch($pattern, $filepath)) {
                        array_push($files, $filepath);
                    }
                } else {
                    array_push($files, $filepath);
                }
            }
        }
        closedir($fh);
    } else {
        // false if the function was called with an invalid non-directory argument
        $files = false;
    }

    return $files;
}