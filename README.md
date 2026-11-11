## Solicitações de férias e ausências

Execute `database/banco.sql` para criar o banco `dboEmpresaMvc`, suas tabelas e os dados de demonstração em uma instalação nova. Embora a criação das tabelas e os inserts iniciais sejam repetíveis, o script não migra nem apaga dados de uma instalação antiga. Faça backup e prepare uma migração específica antes de atualizar um banco existente. O arquivo SQL fica em `database/`, bloqueada para acesso HTTP.

### Primeiro acesso administrativo

Em uma instalação nova, o `database/banco.sql` cria somente dois funcionários de demonstração e uma conta para cada um:

- Administrador: login `luis.admin`, senha `ABC12345`
- Funcionário: login `joao.vitor`, senha `ABC12345`

As senhas são gravadas somente como hashes. Os comandos de criação e seed são repetíveis: executar o arquivo novamente não duplica os registros iniciais. Em um banco que já contém outros funcionários ou contas, o script não apaga esses dados; faça a limpeza manual somente após backup, se realmente quiser removê-los. Troque as credenciais de demonstração antes de disponibilizar o sistema publicamente.

Funcionários autenticados podem enviar férias ou registrar uma ausência, acompanhar o status e cancelar pedidos ainda pendentes. Administradores acessam a mesma opção no menu para aprovar ou recusar pedidos pendentes; recusas exigem uma resposta. Períodos sobrepostos com pedidos pendentes ou aprovados são bloqueados.
