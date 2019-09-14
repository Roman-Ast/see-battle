<?php

namespace seeBattle\entities;

class Ai
{
    private $connection;

    public function __construct()
    {
        $this->connection = pg_connect("host=localhost dbname=aimemory user=roman password=rimma");
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS misses(
                id serial,
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS hits(
                id serial,
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS ships(
                id serial,
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS halo(
                id serial,
                y integer,
                x integer
            );"
        );
    }

    public function shoot()
    {
        $res = pg_query($this->connection, "SELECT * FROM hits;");
        $hits = pg_fetch_all($res);

        $res = pg_query($this->connection, "SELECT * FROM misses;");
        $misses = pg_fetch_all($res);

        $res = pg_query($this->connection, "SELECT * FROM ships;");
        $ships = pg_fetch_all($res);

        $res = pg_query($this->connection, "SELECT * FROM halo;");
        $halo = pg_fetch_all($res);

        $randX = rand(0, 9);
        $randY = rand(0, 9);
        return [ 'x' => 0, 'y' => 0 ];
    }
}