<?php

namespace Itsmng\Doctrine\DQL;

use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Usage: TS_RANK(tsvector, tsquery)
 */
class TsRankFunction extends FunctionNode
{
    private $tsvector;
    private $tsquery;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // TS_RANK
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        // Accept any expression (columns, function calls like TO_TSVECTOR(...), CONCAT(...), etc.)
        $this->tsvector = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);

        $this->tsquery = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "TS_RANK(%s, %s)",
            $this->tsvector->dispatch($sqlWalker),
            $this->tsquery->dispatch($sqlWalker)
        );
    }
}
