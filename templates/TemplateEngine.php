<?php

namespace templates;

class TemplateEngine
{
    public function render(string $templatePath, array $data = []): string {
        if (!file_exists($templatePath)) {
            throw new \Exception("Error: no template found - {$templatePath}");
        }

        extract($data);

        $templateContent = file_get_contents($templatePath);
        $templateContent = str_replace('{{', '<?php', $templateContent);
        $templateContent = str_replace('}}', '?>', $templateContent);
        $templateContent = preg_replace('/@{if\(\s*(.+?)\s*\)/', '<?php if($1): ?>', $templateContent);
        $templateContent = str_replace('@endif', '<?php endif; ?>', $templateContent);
        $templateContent = str_replace('@else', '<?php else ?>', $templateContent);
        $templateContent = preg_replace('/@foreach\(\s*(.+?)\s*\)/', '<?php foreach($1): ?>', $templateContent);
        $templateContent = str_replace('@endforeach', '<?php endforeach; ?>', $templateContent);

        ob_start();
        eval('?>' . $templateContent);
        return ob_get_clean();
    }

    private function parseCustomSyntax(string $template): string {
        $replacements = [
            '/\{\{if (.+?)\}\}/' => '<?php if ($1): ?>',
            '/\{\{else\}\}/' => '<?php else: ?>',
            '/\{\{endif\}\}/' => '<?php endif; ?>',
            '/\{\{foreach (.+?) as (.+?)\}\}/' => '<?php foreach ($1 as $2): ?>',
            '/\{\{endforeach\}\}/' => '<?php endforeach; ?>',
            '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_\->\[\]\'"\(\)]*)\s*\}\}/' => '<?= htmlspecialchars($$1, ENT_QUOTES, "UTF-8") ?>'
        ];
        return preg_replace(array_keys($replacements), array_values($replacements), $template);
    }
}