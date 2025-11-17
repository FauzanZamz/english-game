<?php

namespace App\Services;

class CrosswordBuilder
{
    public function build(array $words, int $size): array
    {
        $grid = array_fill(0,$size,array_fill(0,$size,''));
        $positions = [];

        usort($words, fn($a,$b)=>strlen($b)<=>strlen($a));

        $w = $words[0];
        $startCol = intdiv($size - strlen($w), 2);
        $startRow = intdiv($size, 2);
        $this->placeWord($grid, $w, $startRow, $startCol, 'across');
        $positions[$w] = ['row'=>$startRow,'col'=>$startCol,'direction'=>'across','length'=>strlen($w),'number'=>1];
        $placed = [$w];

        foreach (array_slice($words,1) as $word){
            $best=null; $maxInt=-1;
            foreach ($placed as $pw){
                for ($i=0;$i<strlen($word);$i++){
                    for ($j=0;$j<strlen($pw);$j++){
                        if ($word[$i] === $pw[$j]){
                            $pos = $positions[$pw];
                            if ($pos['direction']==='across'){
                                $r = $pos['row'] - $i; $c = $pos['col'] + $j;
                                if ($this->canPlace($grid,$word,$r,$c,'down',$size)){
                                    $ints = $this->countIntersections($grid,$word,$r,$c,'down');
                                    if ($ints>$maxInt){ $maxInt=$ints; $best=['r'=>$r,'c'=>$c,'dir'=>'down']; }
                                }
                            } else {
                                $r = $pos['row'] + $j; $c = $pos['col'] - $i;
                                if ($this->canPlace($grid,$word,$r,$c,'across',$size)){
                                    $ints = $this->countIntersections($grid,$word,$r,$c,'across');
                                    if ($ints>$maxInt){ $maxInt=$ints; $best=['r'=>$r,'c'=>$c,'dir'=>'across']; }
                                }
                            }
                        }
                    }
                }
            }
            if ($best){
                $this->placeWord($grid, $word, $best['r'], $best['c'], $best['dir']);
                $positions[$word] = ['row'=>$best['r'],'col'=>$best['c'],'direction'=>$best['dir'],'length'=>strlen($word)];
                $placed[]=$word;
            }
        }

        $starts=[];
        foreach ($positions as $wd=>$pos){ $starts[] = [$pos['row'],$pos['col'],$wd]; }
        usort($starts, fn($a,$b)=>$a[0]===$b[0] ? $a[1]<=>$b[1] : $a[0]<=>$b[0]);
        foreach ($starts as $i=>$info){ $positions[$info[2]]['number']=$i+1; }

        return [$grid,$positions];
    }

    private function canPlace(&$grid,$word,$row,$col,$dir,$size){
        if ($row<0||$col<0) return false;
        if ($dir==='across'){
            if ($col+strlen($word) > $size || ($col>0 && $grid[$row][$col-1]) || ($col+strlen($word)<$size && $grid[$row][$col+strlen($word)])) return false;
            for ($i=0;$i<strlen($word);$i++){
                $cell = $grid[$row][$col+$i] ?? null;
                if ($cell && $cell!==$word[$i]) return false;
                if (!$cell){
                    if (($row>0 && $grid[$row-1][$col+$i]) || ($row<$size-1 && $grid[$row+1][$col+$i])) return false;
                }
            }
        } else {
            if ($row+strlen($word) > $size || ($row>0 && $grid[$row-1][$col]) || ($row+strlen($word)<$size && $grid[$row+strlen($word)][$col])) return false;
            for ($i=0;$i<strlen($word);$i++){
                $cell = $grid[$row+$i][$col] ?? null;
                if ($cell && $cell!==$word[$i]) return false;
                if (!$cell){
                    if (($col>0 && $grid[$row+$i][$col-1]) || ($col<$size-1 && $grid[$row+$i][$col+1])) return false;
                }
            }
        }
        return true;
    }
    private function placeWord(&$grid,$word,$row,$col,$dir){
        if ($dir==='across'){ for ($i=0;$i<strlen($word);$i++) $grid[$row][$col+$i]=$word[$i]; }
        else { for ($i=0;$i<strlen($word);$i++) $grid[$row+$i][$col]=$word[$i]; }
    }
    private function countIntersections(&$grid,$word,$row,$col,$dir){
        $n=0;
        if ($dir==='across'){ for ($i=0;$i<strlen($word);$i++) if (($grid[$row][$col+$i]??'')===$word[$i]) $n++; }
        else { for ($i=0;$i<strlen($word);$i++) if (($grid[$row+$i][$col]??'')===$word[$i]) $n++; }
        return $n;
    }
}
