<?php
namespace AppBundle\DQL;

use \Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use \Doctrine\ORM\Query\SqlWalker;
use \Doctrine\ORM\Query\Parser;

/**
 * Class DistanceFunction
 *
 * Usage: get_distance(lat1,long1,lat2,long2): FLOAT
 */
class GetDistanceFunction1 extends FunctionNode
{

    private $latitude1;

    private $longitude1;

    private $latitude2;

    private $longitude2;

//    public function __construct($name)
//    {
//        $this->setName('get_distance');
//        parent::__construct($name);
//    }

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
            $this->latitude1->dispatch($sqlWalker) . ',' .
            $this->longitude1->dispatch($sqlWalker) . ',' .
            $this->latitude2->dispatch($sqlWalker) . ',' .
            $this->longitude2->dispatch($sqlWalker) .
            ')';
    }

//    public function setName($name){
//        $this->name = $name;
//    }
}
