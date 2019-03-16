<?php
namespace app\framework\template;

class TemplateParser {
    // @name
    private $empty_rules = [
        "else", "at", "break"
    ];
    
    // @name(...)
    private $simple_rules = [
        "json", "attr", "echo", "elseif", "raw", "html",
        "include"
    ];
    
    // @name(...) ... @end
    private $compound_rules = [
        "if", "foreach", "component", "slot",
    ];
    
    // @name ... @end
    private $empty_compound_rules = [
        "php", "comment"
    ];
    
    private $source;
    private $i = -1;
    private $c;
    
    public function __construct($source) {
        $this->source = $source;
        $this->advance();
    }
    
    private function advance($match = null) {
        if ($match !== null && $this->c !== $match) {
            throw new Exception("Expected `" . $match . "`");
        }
        $this->i += 1;
        $this->c = mb_substr($this->source, $this->i, 1, "utf8");
        return $this->c;
    }
    
    private function lookAhead($len = 1) {
        return mb_substr($this->source, $this->i, $len);
    }
    
    private function parseName() {
        $name = "";
        $first_char = true;
        while ($this->c >= 'a' && $this->c <= 'z' || $this->c >= 'A' && $this->c <= 'Z' || $this->c === '_'
                || (!$first_char && $this->c >= '0' && $this->c <= '9')) {
            $name .= $this->c;
            $this->advance();
            $first_char = false;
        }
        if (!$name) {
            throw new Exception("Expected name");
        }
        return $name;
    }
    
    private function parseString() {
        $string_val = '';
        $quote = $this->c;
        if ($this->c === '"' || $this->c === "'") {
            $string_val .= $this->c;
            $this->advance();
        } else {
            throw new Exception("Expected ' or \"");
        }
        while ($this->c !== '') {
            switch ($this->c) {
            case '\\':
                $string_val .= $this->c;
                $this->advance();
                if ($this->c !== "") {
                    $string_val .= $this->c;
                    $this->advance();
                } else {
                    throw new Exception("Unterminated string");
                }
                break;
            case $quote:
                $this->advance();
                return $string_val . $quote;
                break;
            default:
                $string_val .= $this->c;
                $this->advance();
                break;
            }
        }
        throw new Exception("Unterminated string");
    }
    
    private function parseGroup($left = "(", $right = ")") {
        $content = "";
        $this->advance($left);
        $parens = 1;
        while ($this->c !== '') {
            switch ($this->c) {
            case '"':
            case "'":
                $content .= $this->parseString();
                break;
            case $left:
                $content .= $this->c;
                $this->advance();
                $parens += 1;
                break;
            case $right:
                $this->advance();
                $parens -= 1;
                if ($parens === 0) {
                    return $content;
                } else {
                    $content .= $right;
                }
                break;
            default:
                $content .= $this->c;
                $this->advance();
                break;
            }
        }
        throw new Exception("Unterminated group");
    }
    
    private function parseRule() {
        $this->advance("@");
        if ($this->c === "(") {
            return new ParseTree("(default)", $this->parseGrouo());
        }
        $name = $this->parseName();
        $is_empty_rule = in_array($name, $this->empty_rules);
        $is_simple_rule = in_array($name, $this->simple_rules);
        $is_compund_rule = in_array($name, $this->compound_rules);
        $is_empty_compound_rule = in_array($name, $this->empty_compound_rules);
        if (!$is_empty_rule && !$is_simple_rule && !$is_compund_rule && !$is_empty_compound_rule) {
            throw new Exception("Unsupported rule: @" . $name);
        }
        $children = null;
        $expression = null;
        if (!$is_empty_rule) {
            if ($this->c === " ") {
                $this->advance(); // optional space
            }
            if (!$is_empty_compound_rule) {
                $expression = $this->parseGroup();
            }
            if (!$is_simple_rule) {
                $children = $this->parse(false);
                $this->parseEndRule();
            }
        }
        return new ParseTree($name, $expression, $children);
    }
    
    private function parseEndRule() {
        $this->advance("@");
        $name = $this->parseName();
        if (mb_substr($name, 0, 3, "utf8") !== "end") {
            throw new Exception("Expected @end...");
        }
    }
    
    public function parse($main = true) {
        $all = [];
        $text = "";
        for (;;) {
            if ($this->lookAhead(4) === "@end" || $this->c === "") {
                if ($main && $this->c !== "") {
                    throw new Exception("Unexpected @end...");
                }
                if ($text) {
                    $all[] = new ParseTree("(text)", $text);
                }
                return $all;
            }
            if ($this->lookAhead(2) === "{{") {
                if ($text) {
                    $all[] = new ParseTree("(text)", $text);
                    $text = '';
                }
                $this->advance("{");
                $all[] = new ParseTree("(braces)", $this->parseGroup("{", "}"));
                $this->advance("}");
            } else if ($this->c === "@") {
                if ($text) {
                    $all[] = new ParseTree("(text)", $text);
                    $text = '';
                }
                $all[] = $this->parseRule();
            } else {
                $text .= $this->c;
                $this->advance();
            }
        }
        // unreachable
    }
}

?>