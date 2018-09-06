<?php

class BinSearcher
{
    private $Res,$FileSize,$Offset,$BufferSize;

    public function __construct($FileName)
    {
        $this->BufferSize=1024;
        if(file_exists($FileName))
        {
            $this->FileSize = filesize($FileName);
            $this->Res = fopen($FileName, 'r');
        }
    }

    public function __destruct()
    {
        if($this->Res) fclose($this->Res);
    }

    private function GetOffset($Offset,$Char)
    {
        fseek($this->Res,$Offset);
        while (!feof($this->Res)){
            $Data=fread($this->Res,$this->BufferSize);
            if($Pos=strpos($Data,$Char)){
                return $Offset+$Pos;
            }
            $Offset+=$this->BufferSize;
        }
        return false;
    }

    private function GetKey($Offset)
    {
        $Start=$this->GetOffset($Offset,0x0A);
        $End=$this->GetOffset($Start,0x09);
        if(!$End) return false;
        fseek($this->Res,$Start);
        return [$End,fread($this->Res,$End-$Start)];
    }

    public function GetValue($Key)
    {
        if(!$this->Res) return null;
        $Div=2;
        $PrevOffset=-1;
        $this->Offset=round($this->FileSize / $Div);
        while($PrevOffset !== $this->Offset)
        {
            $Result = $this->GetKey($this->Offset);
            $Div *= 2;
            $Compare=strnatcmp($Result[1], $Key);
            if ($Compare === 0)
            {
                $EndValue=$this->GetOffset($Result[0],0x0A);
                fseek($this->Res,$Result[0]);
                return fread($this->Res,$EndValue-$Result[0]);
            }
            else
            {
                $PrevOffset=$this->Offset;
                $this->Offset += round($this->FileSize/$Div)*$Compare*-1;
            }
        }
        return null;
    }
}