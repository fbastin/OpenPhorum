<?php if (!defined("PHORUM")) return; ?>

// MathJax configuration and loading (bbcode_math module)
(function() {
    if (window.MathJax) return;
    window.MathJax = {
        tex: {
            inlineMath: [['\\(', '\\)']],
            displayMath: [['$$', '$$']],
            processEscapes: true
        },
        options: {
            skipHtmlTags: ['script','noscript','style','textarea','pre','code']
        }
    };
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js';
    s.async = true;
    document.head.appendChild(s);
})();
