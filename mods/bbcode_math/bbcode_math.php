<?php
///////////////////////////////////////////////////////////////////////////////
// BBcode + Math module for Phorum
// Adds [math]...[/math] and [displaymath]...[/displaymath] tags that render
// LaTeX mathematics via MathJax. Runs after BBcode so it does not conflict.
///////////////////////////////////////////////////////////////////////////////

if (!defined("PHORUM")) return;

function phorum_mod_bbcode_math_format($data)
{
    foreach ($data as $id => $message)
    {
        if (!isset($message['body'])) continue;

        $body = $message['body'];

        // [displaymath]...[/displaymath] -> $$...$$
        $body = preg_replace_callback(
            '!\[displaymath\](.*?)\[/displaymath\]!si',
            function ($m) {
                $tex = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
                $tex = strip_tags($tex);
                return '<div class="mathjax-block">$$' . htmlspecialchars($tex, ENT_NOQUOTES, 'UTF-8') . '$$</div>';
            },
            $body
        );

        // [math]...[/math] -> $...$  (inline)
        $body = preg_replace_callback(
            '!\[math\](.*?)\[/math\]!si',
            function ($m) {
                $tex = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
                $tex = strip_tags($tex);
                return '<span class="mathjax-inline">\\(' . htmlspecialchars($tex, ENT_NOQUOTES, 'UTF-8') . '\\)</span>';
            },
            $body
        );

        $data[$id]['body'] = $body;
    }

    return $data;
}

function phorum_mod_bbcode_math_javascript_register($data)
{
    $data[] = array(
        'module' => 'bbcode_math',
        'source' => 'file(mods/bbcode_math/javascript.php)',
        'cache_key' => '1.0.0'
    );
    return $data;
}
