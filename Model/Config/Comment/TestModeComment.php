<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Config\Comment;

class TestModeComment implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue): string
    {
        return '<span style="display: block; margin-bottom: 8px;">'
            . __('If you don\'t have a Przelewy24 account')
            . ', <a href="https://panel.przelewy24.pl/rejestracja.php">' . __('register here') . '</a>. '
            . __('After registration, you\'ll receive the necessary configuration details.')
            . '</span>'
            . '<span style="display: block;">'
            // phpcs:ignore
            . __('To test orders, switch the module mode to sandbox and enter the CRC key and API key from the test panel. After testing, replace the key with the production one.')
            // phpcs:ignore
            . ' ' . __('<a href="https://developers.przelewy24.pl/index.php?en#tag/Set-up-and-test-your-accounts/Create-your-sandbox-account-(sandbox)">Sandbox activation guide</a>')
            . '</span>';
    }
}
