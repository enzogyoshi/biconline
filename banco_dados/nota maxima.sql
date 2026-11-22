insert into publicacao(conteudo,url_midia,link,data_pub,id_usuario)
values('publicacao de teste 10', null, null, '2026-10-29', 1)

SELECT id,conteudo,data_pub,id_usuario 
from publicacao
where id_usuario = 1

select 
publicacao.id as id_publicacao,
publicacao.conteudo,
publicacao.data_pub,
usuario.id as id_usuario,
usuario.nome as nome_usuario
from publicacao
inner join usuario
on publicacao.id_usuario = usuario.id
where usuario.id =1





