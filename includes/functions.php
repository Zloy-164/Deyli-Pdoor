<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';
function e(?string $v):string{return htmlspecialchars($v??'',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function url(string $p=''):string{return rtrim(BASE_URL,'/').'/'.ltrim($p,'/');}
function redirect(string $p):never{header('Location: '.url($p));exit;}
function excerpt(string $t,int $n=160):string{$t=trim(preg_replace('/\s+/',' ',strip_tags($t))??'');return mb_strlen($t)<=$n?$t:rtrim(mb_substr($t,0,$n-1)).'…';}
function format_date(string $d):string{return date('d.m.Y H:i',strtotime($d));}
function categories():array{return db()->query('SELECT id,name,slug FROM categories ORDER BY name')->fetchAll();}
function slugify(string $t):string{$t=mb_strtolower(trim($t));$m=['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'];$t=strtr($t,$m);$t=preg_replace('/[^a-z0-9]+/i','-',$t)??'';return trim($t,'-')?:'post-'.time();}
function unique_slug(string $slug,?int $ignore=null):string{$base=$slug;$n=2;while(true){$s=$ignore?db()->prepare('SELECT id FROM posts WHERE slug=? AND id<>? LIMIT 1'):db()->prepare('SELECT id FROM posts WHERE slug=? LIMIT 1');$s->execute($ignore?[$slug,$ignore]:[$slug]);if(!$s->fetch())return $slug;$slug=$base.'-'.$n++;}}
function is_admin():bool{return !empty($_SESSION['user_id'])&&($_SESSION['role']??'')==='admin';}
function require_admin():void{if(!is_admin())redirect('admin/login.php');}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function verify_csrf():void{$t=$_POST['csrf_token']??'';if(!$t||!hash_equals($_SESSION['csrf']??'',$t)){http_response_code(419);exit('CSRF token mismatch.');}}
function flash(string $k,?string $v=null):?string{if($v!==null){$_SESSION['flash'][$k]=$v;return null;}$x=$_SESSION['flash'][$k]??null;unset($_SESSION['flash'][$k]);return $x;}


function handle_image_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Ошибка загрузки изображения.');
    }

    if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
        throw new RuntimeException('Файл слишком большой. Максимум 8 МБ.');
    }

    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Некорректный загруженный файл.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Допустимы JPG, PNG, WEBP и GIF.');
    }

    $dir = getenv('UPLOAD_DIR') ?: (__DIR__ . '/../uploads');

    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Не удалось создать папку для загрузок.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $target = rtrim($dir, '/') . '/' . $name;

    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Не удалось сохранить изображение.');
    }

    return url('uploads/' . $name);
}
