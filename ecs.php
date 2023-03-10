<?php declare(strict_types=1);
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, ['header' => '(c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.', 'separate' => 'bottom', 'location' => 'after_declare_strict', 'comment_type' => 'comment']);
    $ecsConfig->ruleWithConfiguration(NativeFunctionInvocationFixer::class, [
        'include' => [NativeFunctionInvocationFixer::SET_ALL],
        'scope' => 'namespaced',
    ]);
    $ecsConfig->rule(MbStrFunctionsFixer::class);
    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::CONTROL_STRUCTURES,
        //SetList::STRICT,
        SetList::PSR_12,
    ]);
    $ecsConfig->skip([
        HeaderCommentFixer::class => [
            __DIR__ . '/src/woocommerce-gateway-ratenkaufbyeasycredit/woocommerce-gateway-ratenkaufbyeasycredit.php',
        ]
    ]);
    $parameters = $ecsConfig->parameters();
    $parameters->set(Option::CACHE_DIRECTORY, __DIR__ . '/var/cache/cs_fixer');
    //$parameters->set(Option::CACHE_NAMESPACE, 'EasyCreditRatenkauf');
    $parameters->set(Option::PATHS, [__DIR__ . '/src']);
};
