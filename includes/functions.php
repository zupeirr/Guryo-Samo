<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function clean($v) { return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function formatPrice($p) { return '$' . number_format((float)$p, 0); }
function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdminLoggedIn() { return isset($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', ['admin','staff'], true); }
function redirect($url) { header('Location: ' . $url); exit; }
function requireLogin() { if (!isAdminLoggedIn()) redirect('../login.php'); }
function csrfToken() { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verifyCsrf($t) { return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t ?? ''); }
function uploadPropertyImage($fn, $dir) {
    if (!isset($_FILES[$fn]) || $_FILES[$fn]['error'] === UPLOAD_ERR_NO_FILE) return 'no-image.jpg';
    $f = $_FILES[$fn];
    if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed.'];
    $fi = finfo_open(FILEINFO_MIME_TYPE); $m = finfo_file($fi, $f['tmp_name']); finfo_close($fi);
    if (!in_array($m, ['image/jpeg','image/png','image/webp','image/gif'], true)) return ['error' => 'Invalid image type.'];
    if ($f['size'] > 5*1024*1024) return ['error' => 'Image too large (max 5MB).'];
    $ext = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)));
    $n = 'property_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
    if (!move_uploaded_file($f['tmp_name'], rtrim($dir,'/').'/'.$n)) return ['error' => 'Could not save image.'];
    return $n;
}
function uploadPropertyImages($fn, $dir) {
    if (!isset($_FILES[$fn]) || empty($_FILES[$fn]['name'][0])) return [];
    $fs = $_FILES[$fn]; $c = count($fs['name']); $s = []; $al = ['image/jpeg','image/png','image/webp','image/gif'];
    for ($i=0;$i<$c;$i++) {
        if ($fs['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($fs['error'][$i] !== UPLOAD_ERR_OK) return ['error'=>'Upload failed.'];
        $fi=finfo_open(FILEINFO_MIME_TYPE); $m=finfo_file($fi,$fs['tmp_name'][$i]); finfo_close($fi);
        if (!in_array($m,$al,true)) return ['error'=>'Invalid image type.'];
        if ($fs['size'][$i]>5*1024*1024) return ['error'=>'Image too large.'];
        $ext=preg_replace('/[^a-z0-9]/','',strtolower(pathinfo($fs['name'][$i],PATHINFO_EXTENSION)));
        $n='property_'.time().mt_rand(100,999).'_'.bin2hex(random_bytes(4)).'.'.$ext;
        if (!move_uploaded_file($fs['tmp_name'][$i],rtrim($dir,'/').'/'.$n)) return ['error'=>'Could not save image.'];
        $s[]=$n;
    }
    return $s;
}
function uploadPropertyVideo($fn, $dir) {
    if (!isset($_FILES[$fn]) || $_FILES[$fn]['error'] === UPLOAD_ERR_NO_FILE) return '';
    $f=$_FILES[$fn]; $al=['video/mp4','video/webm','video/ogg','video/quicktime'];
    if ($f['error'] !== UPLOAD_ERR_OK) return ['error'=>'Video upload failed.'];
    $fi=finfo_open(FILEINFO_MIME_TYPE); $m=finfo_file($fi,$f['tmp_name']); finfo_close($fi);
    if (!in_array($m,$al,true)) return ['error'=>'Only MP4/WEBM/OGG/MOV allowed.'];
    if ($f['size']>100*1024*1024) return ['error'=>'Video must be under 100MB.'];
    $ext=preg_replace('/[^a-z0-9]/','',strtolower(pathinfo($f['name'],PATHINFO_EXTENSION)));
    $n='video_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
    if (!move_uploaded_file($f['tmp_name'],rtrim($dir,'/').'/'.$n)) return ['error'=>'Could not save video.'];
    return $n;
}
function savePropertyMedia($conn, $pid, $files, $type='image') {
    $s=$conn->prepare('INSERT INTO property_media (property_id, file_name, media_type, sort_order) VALUES (?,?,?,?)');
    foreach ($files as $i=>$name) { $s->bind_param('issi',$pid,$name,$type,$i); $s->execute(); }
}
function paginate(int $total, int $perPage=20):array {
    $cur=max(1,(int)($_GET['page']??1)); $tp=max(1,(int)ceil($total/$perPage)); $cur=min($cur,$tp);
    return ['total'=>$total,'per_page'=>$perPage,'current'=>$cur,'total_pages'=>$tp,'offset'=>($cur-1)*$perPage];
}
function renderPagination(array $p, string $bu, array $ex=[]):string {
    if ($p['total_pages']<=1) return '';
    $pa=$ex; $l='<div class="pagination">';
    if ($p['current']>1) { $pa['page']=$p['current']-1; $l.='<a href="'.$bu.'?'.http_build_query(array_filter($pa)).'" class="page-btn">‹ Prev</a>'; }
    $st=max(1,$p['current']-2); $en=min($p['total_pages'],$p['current']+2);
    for ($i=$st;$i<=$en;$i++) { $pa['page']=$i; $ac=$i===$p['current']?' active':''; $l.='<a href="'.$bu.'?'.http_build_query(array_filter($pa)).'" class="page-btn'.$ac.'">'.$i.'</a>'; }
    if ($p['current']<$p['total_pages']) { $pa['page']=$p['current']+1; $l.='<a href="'.$bu.'?'.http_build_query(array_filter($pa)).'" class="page-btn">Next ›</a>'; }
    $l.='</div>'; return $l;
}
function getSetting($conn,$key,$default='') {
    $s=$conn->prepare('SELECT setting_value FROM settings WHERE setting_key=?');
    if ($s) { $s->bind_param('s',$key); $s->execute(); $r=$s->get_result(); if ($r&&$r->num_rows>0) { $row=$r->fetch_assoc(); return $row['setting_value']!==null?$row['setting_value']:$default; } }
    return $default;
}
