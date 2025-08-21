<?php
    namespace Itsmng\Doctrine\DQL;

    use Doctrine\ORM\Query\TokenType;
    use Doctrine\ORM\Query\AST\Functions\FunctionNode;
    use Doctrine\ORM\Query\Parser;
    use Doctrine\ORM\Query\SqlWalker;

    /**
     * Usage in DQL: TO_TSVECTOR('french', fieldOrExpression)
     */
    class ToTsVectorFunction extends FunctionNode
    {
        /** @var \Doctrine\ORM\Query\AST\Node|null */
        private $config;

        /** @var \Doctrine\ORM\Query\AST\Node|null */
        private $field;

        public function parse(Parser $parser): void
        {
            // Expect identifier TO_TSVECTOR
            $parser->match(TokenType::T_IDENTIFIER);
            $parser->match(TokenType::T_OPEN_PARENTHESIS);

            // language config: can be a string literal or an expression
            $this->config = $parser->StringPrimary();

            $parser->match(TokenType::T_COMMA);

            // field can be a column reference, CONCAT(...), etc.
            // Accept a full arithmetic/expression so functions like CONCAT(...) are parsed correctly
            $this->field = $parser->ArithmeticExpression();

            $parser->match(TokenType::T_CLOSE_PARENTHESIS);
        }

        public function getSql(SqlWalker $sqlWalker): string
        {
            return sprintf(
                'TO_TSVECTOR(%s, %s)',
                $this->config->dispatch($sqlWalker),
                $this->field->dispatch($sqlWalker)
            );
        }
    }