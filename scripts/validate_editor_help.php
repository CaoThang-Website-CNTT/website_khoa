<?php
$path = dirname(__DIR__) . '/public/help/manifest.json';
$data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$allowed = ['heading','paragraph','list','steps','callout','image','link']; $ids=[]; $errors=[];
foreach (($data['articles'] ?? []) as $i => $article) {
  foreach (['id','title','summary','scope','category','keywords','order','related','content'] as $key) if (!array_key_exists($key,$article)) $errors[]="Article #$i thiếu $key";
  if (isset($article['id']) && in_array($article['id'],$ids,true)) $errors[]="ID trùng: {$article['id']}"; else $ids[]=$article['id']??'';
  foreach (($article['content']??[]) as $block) if (!in_array($block['type']??'', $allowed,true)) $errors[]="{$article['id']}: block không hỗ trợ";
}
foreach (($data['articles']??[]) as $article) foreach (($article['related']??[]) as $id) if (!in_array($id,$ids,true)) $errors[]="{$article['id']}: related không tồn tại: $id";
if ($errors) { fwrite(STDERR, implode(PHP_EOL,$errors).PHP_EOL); exit(1); } echo 'Editor help hợp lệ: '.count($ids).' articles'.PHP_EOL;
