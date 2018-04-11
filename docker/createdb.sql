create database tubedb;

create user tube with login password 'tube';
grant all privileges on database tubedb to tube;

\c tubedb tube;
create table media (
	id serial primary key,
	name text not null,
	path text not null unique,
	thumbnail bytea,
	type text
);
create index media_type_idx on media ( type );
create index media_path_idx on media ( path );
create index media_name_idx on media ( name );

