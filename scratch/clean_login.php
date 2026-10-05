<?php
$filePath = __DIR__ . '/../resources/views/auth/login.blade.php';
$content = file_get_contents($filePath);

// 1. Remove <style>...</style> block
$content = preg_replace('/\s*<style>.*?<\/style>\s*/s', "\n", $content);

// 2. Update x-data="loginForm()"
$newXData = 'x-data="loginForm({
        step: {{ isset($prefilledUser) && $prefilledUser ? 2 : 1 }},
        email: @json(old(\'email\', $prefilledUser[\'email\'] ?? \'\')),
        userInfo: @json($prefilledUser ?? null),
        checkEmailUrl: \'{{ route(\'auth.check-email\') }}\',
        csrfToken: \'{{ csrf_token() }}\'
    })"';

$content = str_replace('x-data="loginForm()"', $newXData, $content);

// 3. Remove <script> function loginForm() ... </script> block at bottom
$content = preg_replace('/\s*<script>\s*function loginForm\(\).*?<\/script>\s*/s', "\n", $content);

file_put_contents($filePath, $content);
echo "SUCCESS - File updated, new length: " . strlen($content) . "\n";
