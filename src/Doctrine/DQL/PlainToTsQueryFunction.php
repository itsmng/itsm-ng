<?php
namespace Itsmng\Doctrine\DQL;

use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Usage: PLAINTO_TSQUERY('french', :search)
 */
class PlainToTsQueryFunction extends FunctionNode
{
    private $config;
    private $query;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // PLAINTO_TSQUERY
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

    $this->config = $parser->StringPrimary(); // language
    $parser->match(TokenType::T_COMMA);

    // Accept expressions for the query (could be a parameter or expression)
    $this->query = $parser->ArithmeticExpression(); // param
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "PLAINTO_TSQUERY(%s, %s)",
            $this->config->dispatch($sqlWalker),
            $this->query->dispatch($sqlWalker)
        );
    }
}