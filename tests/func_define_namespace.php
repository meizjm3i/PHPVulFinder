<?php

namespace subject1{
    function test($s){
        echo $s;
    }
}

namespace subject2{
    function test($s){
        var_dump($s);
    }
}

namespace {
    subject1\test("ss");
    subject2\test("qq");
}


