<?php

use Searchit\Jobs\Models\Job;
use Searchit\Jobs\Models\Category;
use Cms\Classes\Theme;

Route::get('sitemap.xml', function() {

    $jobs = Job::all();
    $categories = Category::all();
    $pages = [];

    $theme = Theme::getActiveTheme();
    $theme_path = $theme->getPath();
    $static_pages_en =  $theme_path.'/content/static-pages';
    $files_en = File::allFiles($static_pages_en);
    
    foreach ($files_en as $file)
    {
        $lines = [];
        $tempArr = [];
        $contents = fopen($file, 'r');

        while (!feof($contents)) {
            $line = fgets($contents);
            $line = trim($line);
            $lines[] = $line;
        }

        foreach($lines as $line) {
            if(strpos($line, 'url = ') !== false) {
                $tempArr['en'] = preg_split('/"/', $line)[1];
            }
            if(strpos($line, 'localeUrl[nl] = ') !== false) {
                $tempArr['nl'] = preg_split('/"/', $line)[1];
            }
        }

        $pages[] = $tempArr;
    }

    return Response::view('searchit.breadcrumbs::sitemap', [
        'jobs' => $jobs,
        'categories' => $categories,
        'pages' => $pages
    ])->header('Content-type', 'text/xml; charset="utf-8"');

});