CREATE VIEW
IF NOT EXISTS teams_cnt AS
SELECT count() as teams_cnt from teams;

CREATE VIEW
  IF NOT EXISTS next_team AS
select t.user_id, t.row_num%c.teams_cnt + 1 as team_id
from (
  SELECT
    (SELECT
      count(DISTINCT b.user_id)
      FROM users b
      WHERE
      a.sex = b.sex AND
      a.user_id >= b.user_id) as row_num,
    a.user_id
  FROM users a) t,
  teams_cnt c;

CREATE VIEW
  IF NOT EXISTS teams_cnt_users AS
select team_id, count(user_id) as cnt 
from users 
group by team_id
ORDER by cnt asc;

CREATE VIEW
  IF NOT EXISTS min_team AS
select team_id, count(user_id) as cnt from users group by team_id order by cnt asc limit 1;

CREATE VIEW
  IF NOT EXISTS max_team AS
select team_id, count(user_id) as cnt from users group by team_id order by cnt desc limit 1;

CREATE VIEW
  IF NOT EXISTS diff_teams AS
select COALESCE(
	(SELECT a.cnt - b.cnt
  from max_team a, min_team b
  where a.cnt >= 10), 0) as diff;

CREATE VIEW
  IF NOT EXISTS stat_users AS
SELECT
      COUNT(user_id) AS users_count,
      count(distinct team_id) AS assigned_teams
    FROM
      users;

CREATE VIEW
  IF NOT EXISTS stat_males AS
SELECT
      COUNT(user_id) AS male_count
    FROM
      users
    WHERE
      sex = 'male';

CREATE VIEW
  IF NOT EXISTS stat_min_users AS
SELECT
      team_id,
      COUNT(user_id) AS min_users_in_teams
    FROM
      users
    GROUP BY
      team_id
    ORDER BY
      COUNT(user_id) ASC
    LIMIT
      1;

CREATE VIEW
  IF NOT EXISTS stat_all AS
SELECT
  a.users_count,
  100.0 * b.male_count / a.users_count AS male_share,
  a.assigned_teams, c.min_users_in_teams, d.diff
FROM
  stat_users a,
  stat_males b,
  stat_min_users c,
  diff_teams d;

