-- Active: 1769630691148@@127.0.0.1@5432


select * from users u
join collocation_user cu
on u.id = cu.user_id
join collocations c
on cu.collocation_id = c.id;
