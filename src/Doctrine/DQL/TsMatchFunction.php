<?php

namespace Itsmng\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class TsMatchFunction extends FunctionNode
{
    private $left;
    private $right;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // TS_MATCH
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        // Accept full expressions (columns, functions, CONCAT(...), etc.)
        $this->left  = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->right = $parser->ArithmeticExpression();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '(%s @@ %s)',
            $this->left->dispatch($sqlWalker),
            $this->right->dispatch($sqlWalker)
        );
    }
}
