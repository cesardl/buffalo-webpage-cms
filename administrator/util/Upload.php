<?php

/*
 * Validated file uploads for the administrator.
 *
 * The action controllers used to carry their own near-identical copies of
 * uploadPhoto() and uploadPDF(). Both decided whether a file was safe from
 * $_FILES[...]['type'] -- a header the browser sends and a caller picks
 * freely -- and then wrote the file into the web root under the name it
 * arrived with. A PHP script announced as image/gif was accepted and landed
 * somewhere Apache would happily execute it.
 *
 * These read the bytes on disk instead, require an extension we recognise,
 * and never reuse the submitted name.
 *
 * Return contract is unchanged, because the callers still index into their
 * own $messages array with it:
 *   ''        no file was submitted, keep whatever path is already stored
 *   0         rejected: wrong extension, wrong contents, or too large
 *   1         moved, but nothing arrived at the destination
 *   2         not an uploaded file, or the move failed
 *   string    the web-relative path to store in the database
 */

define('UPLOAD_MAX_BYTES', 5000000);

function upload_image($file, $subdir)
{
    return upload_validated($file, $subdir, array('gif', 'jpg', 'jpeg', 'png'));
}

function upload_pdf($file, $subdir)
{
    return upload_validated($file, $subdir, array('pdf'));
}

function upload_validated($file, $subdir, $allowed_extensions)
{
    if (!isset($file['size']) || $file['size'] == 0) {
        return '';
    }
    if ($file['size'] >= UPLOAD_MAX_BYTES) {
        return 0;
    }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return 2;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions, true)) {
        return 0;
    }
    if (!upload_contents_match($file['tmp_name'], $extension)) {
        return 0;
    }

    $name = upload_safe_name($file['name'], $extension);
    $destination = dirname(__FILE__) . '/../../' . $subdir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return 2;
    }
    return file_exists($destination) ? $subdir . '/' . $name : 1;
}

/**
 * The extension says what Apache will do with the file; this says what the
 * file actually is. Both have to agree, so a .gif that is really a script
 * is rejected and a script named .php never gets as far as this check.
 */
function upload_contents_match($path, $extension)
{
    if ($extension === 'pdf') {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }
        $magic = fread($handle, 5);
        fclose($handle);
        return $magic === '%PDF-';
    }

    $image = @getimagesize($path);
    if ($image === false) {
        return false;
    }

    $expected = array(
        'gif' => IMAGETYPE_GIF,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
    );
    return isset($expected[$extension]) && $image[2] === $expected[$extension];
}

/**
 * Keeps something recognisable from the original name for the admin's sake,
 * but the stored name is ours: the submitted one decides both where the file
 * lands and whether it is executable.
 */
function upload_safe_name($original, $extension)
{
    $base = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
    $base = trim(substr($base, 0, 60), '_');
    if ($base === '') {
        $base = 'archivo';
    }
    return $base . '_' . substr(md5(uniqid('', true)), 0, 8) . '.' . $extension;
}
