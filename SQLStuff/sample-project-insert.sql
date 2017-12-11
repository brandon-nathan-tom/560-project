with watch_org as (
	 insert into watchables
	 default values
	 returning id
),
watch_proj as (
	 insert into watchables
	 default values
	 returning id
),
org_insert as (
	 insert into organizations (id, name, email)
	 select watch_org.id, 'Suckless', 'dev@suckless.org'
	 from watch_org
	 returning id
)
insert into projects (id, name, repo, owner_id)
select watch_proj.id, 'dwm', 'https://git.suckless.org/dwm', watch_org.id
from watch_org, watch_proj;
