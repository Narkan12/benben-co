<?php
/**
 * Generates a simple SVG placeholder image for missing product images
 */
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">';
echo '<rect width="400" height="400" fill="#F5EBDD"/>';
echo '<text x="200" y="185" font-family="Georgia,serif" font-size="56" text-anchor="middle" fill="#B58A5A">🥐</text>';
echo '<text x="200" y="235" font-family="Georgia,serif" font-size="18" text-anchor="middle" fill="#A8754F">No Image</text>';
echo '</svg>';
