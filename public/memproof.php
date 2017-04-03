<?php

memprof_enable();

$var = array_fill(0, 100, 'test');

memprof_dump_pprof(fopen("/tmp/cachegrindout/profile.heap", "w"));