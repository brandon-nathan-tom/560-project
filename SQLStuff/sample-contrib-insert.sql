with watch_contrib as (
	 insert into watchables
	 default values
	 returning id
)
insert into contributors (id, name, email)
select watch_contrib.id, 'Brandon Jansen', 'brandon.jansen63@gmail.com'
from watch_contrib;
