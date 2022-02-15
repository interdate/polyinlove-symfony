<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use \Doctrine\ORM\Query\AST\Functions\FunctionNode;
use \Doctrine\ORM\Query\SqlWalker;
use \Doctrine\ORM\Query\Parser;

/**
 * Class DistanceFunction
 * @package AppBundle\Entity
 *
 * Usage: get_distance(lat1, long1, lat2, long2): float
 */
class GetDistanceFunction extends FunctionNode
{
    /** @var Node */
    private $latitude1;
    /** @var Node */
    private $longitude1;
    /** @var Node */
    private $latitude2;
    /** @var Node */
    private $longitude2;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->latitude1 = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->longitude1 = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_COMMA); // (7)
        $this->latitude2 = $parser->ArithmeticPrimary(); // (8)
        $parser->match(Lexer::T_COMMA); // (9)
        $this->longitude2 = $parser->ArithmeticPrimary(); // (10)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        // TODO: Implement parse() method.
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        // TODO: Implement getSql() method.
        return 'get_distance(' .
            $this->latitude1->dispatch($sqlWalker) . ', ' .
            $this->longitude1->dispatch($sqlWalker) . ', ' .
            $this->latitude2->dispatch($sqlWalker) . ', ' .
            $this->longitude2->dispatch($sqlWalker) .
            ')';
    }
}