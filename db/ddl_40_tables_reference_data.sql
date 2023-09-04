insert into locations(location_id) values (1), (2), (3), (4), (5);
insert into stations(station_id) values (1), (2), (3), (4), (5), (6), (7), (8), (9), (10);
insert into directions(direction) values ('clockwise'), ('backward');

INSERT INTO teams (location_id, station_id, direction, image_path)
  select *, 'images/image_' || a.location_id || '_' || c.direction || '.png' 
  from locations a, stations b, directions c;

delete from teams
  WHERE (location_id, station_id) in (
    (1, 7), (3, 1), (3, 5), (5, 5), (5, 7)
  );

