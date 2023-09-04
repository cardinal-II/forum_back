CREATE TABLE IF NOT EXISTS
  teams (
    team_id INTEGER PRIMARY KEY autoincrement,
    location_id INTEGER NOT NULL on conflict abort CHECK (location_id >= 1),
    station_id INTEGER NOT NULL on conflict abort CHECK (station_id >= 1),
    direction TEXT NOT NULL on conflict abort CHECK (direction = 'clockwise' or direction = 'backward'),
    image_path text NOT NULL on conflict abort,
    UNIQUE (location_id, station_id, direction) on conflict abort
  );

CREATE TABLE IF NOT EXISTS
  users (
    user_id INTEGER PRIMARY KEY autoincrement,
    uuid text UNIQUE on conflict abort,
    sex text not null on conflict abort CHECK (sex = 'male' or sex = 'female'),
    team_id INTEGER DEFAULT NULL CHECK (team_id >= 1)
  );

create table IF NOT EXISTS locations(location_id INTEGER NOT NULL);
create table IF NOT EXISTS stations(station_id INTEGER NOT NULL);
create table IF NOT EXISTS directions(direction text NOT NULL);

