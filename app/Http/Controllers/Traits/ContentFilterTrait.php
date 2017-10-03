<?php
/**
 * Replace <a href=""></a> tags to <a [routerLink]=""></a>
 */

namespace App\Http\Controllers\Traits;

trait ContentFilterTrait
{
    /**
     * Filter on any saved content such as post content, topic content
     * This function is used before all save actions
     * @param $input
     * @param $base_url
     * @return string
     */
    public function htmlFilter($input, $base_url)
    {
        $content = $input;

        // Normalize html: remove all html tags except <h[1-6]>, <p>, <a>, <img>, <ul>,  <ol>, <li>
        $regexp = '/<[^\/hpaioul]+[^>]*>/'; // opening tag
        $content = preg_replace($regexp, '', $content);
        $regexp = '/<\/[^hpaioul]+[^>]*>/'; // closing tag
        $content = preg_replace($regexp, '', $content);

        // Strip image size, style/class from <img>, <p> <h[1-6]>, <a> etc
        $regexp = '/(<[ipha]+)\s*height\s*=\s*"(.+?)"/'; // image height
        $content = preg_replace($regexp, '${1}', $content);
        $regexp = '/(<[ipha]+)\s*width\s*=\s*"(.+?)"/'; // image width
        $content = preg_replace($regexp, '${1}', $content);
        $regexp = '/(<[ipha]+[1-6]?)\s*class\s*=\s*"(.+?)"/'; // class
        $content = preg_replace($regexp, '${1}', $content);
        $regexp = '/(<[ipha]+[1-6]?)\s*style\s*=\s*"(.+?)"/'; // style
        $content = preg_replace($regexp, '${1}', $content);

        // Normalize html: Replace <h[1-6]>abc<img src=""[/]>xyz</h[1-6]> to sth like:
        // <img src=""[/]>
        // <h[1-6]>abcxyz</h[1-6]>
        $regexp = '/(<h[1-6]>)[\s\t]*([^<]*)(<img[^>]*[\/]?>)[\s\t]*([^<]*)(<\/h[1-6]>)/';
        $content = preg_replace($regexp, "$3\n$1$2$4$5", $content);

        // FIXME: pattern [^h]* can not guaranty matching until href="", say:
        // <a class="hello" href="">, [^h]* will match 'h' from hello.

        // Replace <a yy="yy" href="/xxx/yyy/zzz" xx="xx"> to
        // <a href="/xxx/yyy/zzz" routerLink="/xxx/yyy/zzz" yy="yy" xx="xx">,
        $regexp = '/<a\s*([^h]*)href\s*=\s*"\s*(\/[^"\s]*)\s*"\s*([^>]*)>/';
        $content = preg_replace($regexp, '<a href="${2}" routerLink="${2}" ${1}${3}>', $content);

        // Replace <a xx="xx" href="http://www.bangli.uk/xxx/yyy/zzz" yy="yy"> to
        // <a href="/xxx/yyy/zzz" routerLink="/xxx/yyy/zzz" xx="xx" yy="yy">,
        $regexp = '/<a\s*([^h]*)href\s*=\s*"\s*http[s]?:\/\/' . preg_quote($base_url) . '(\/[^"\s]*)\s*"\s*([^>]*)>/';
        $content = preg_replace($regexp, '<a href="${2}" routerLink="${2}" ${1}${3}>', $content);

        // TODO: Strip target="_blank" on internal link

        return $content;
    }
}