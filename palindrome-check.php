<?php

$stringToCheck = "madam";

function isPalindrome($str) {
    // Remove spaces and convert to lowercase
    $str = strtolower(str_replace(' ', '', $str));
    
    // Reverse the string
    $reversedStr = strrev($str);
    
    // Compare the original string with the reversed string
    return $str === $reversedStr;
}

if (isPalindrome($stringToCheck)) {
    echo "$stringToCheck is a palindrome.";
} else {
    echo "$stringToCheck is not a palindrome.";
}