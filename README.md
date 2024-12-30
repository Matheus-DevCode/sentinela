# Tutorial de Execução do Projeto

## Requisitos Pré-requisitos

- Certifique-se de ter o [Docker](https://www.docker.com/) e o [Docker Compose](https://docs.docker.com/compose/) instalados em sua máquina.
- Certifique-se de que o comando `ngrok` está disponível. Caso não esteja, instale-o utilizando:

```bash
sudo snap install ngrok
```

---

## Passo a Passo para Rodar o Projeto

### 1. Clone o Repositório

Primeiro, baixe o projeto para sua máquina local:

```bash
git clone <URL-do-seu-repositorio>
cd <nome-do-diretorio-clonado>
```

### 2. Execute o Docker Compose

No diretório raiz do projeto, execute o seguinte comando:

```bash
docker compose up -d --build
```

#### O que este comando faz?

1. **`docker compose up`**: Cria e inicia os contêineres definidos no arquivo `docker-compose.yml`.
2. **`-d`**: Roda os contêineres em segundo plano (modo "detached").
3. **`--build`**: Recria as imagens dos contêineres antes de iniciá-los, garantindo que todas as alterações recentes no projeto sejam refletidas.

### 3. Expor seu Servidor Local (usando ngrok)

Inicie o ngrok apontando para a porta onde o seu servidor está rodando (por exemplo, porta 8000):

```bash
ngrok http 8000
```

#### Caso ocorra erro de autenticação:
Se ao executar o comando aparecer a mensagem `authentication failed: Usage of ngrok requires a verified account and authtoken`, siga os passos abaixo:

1. Acesse o site do [ngrok](https://ngrok.com/) e cadastre-se.
2. Copie seu token de autenticação, disponível na seção "Your Authtoken".
3. Configure o token em sua máquina executando o seguinte comando:

```bash
ngrok config add-authtoken $YOUR_AUTHTOKEN
```

Substitua `$YOUR_AUTHTOKEN` pelo token gerado na sua conta do ngrok.

### 4. Ativar o envio de mensagem 

Quando expor a aplicação vai aparecer esse mensagem 
                                                                                                                                                                                                                                                                                                                                        

`
Sign up to try new private endpoints https://ngrok.com/new-features-update?ref=private                                                                                            
                                                                                                                                                                                    
Session Status                online                                                                                                                                                
Account                       Matehus-Dev (Plan: Free)                                                                                                                              
Update                        update available (version 3.19.0, Ctrl-U to update)                                                                                                   
Version                       3.18.4                                                                                                                                                
Region                        South America (sa)                                                                                                                                    
Latency                       66ms                                                                                                                                                  
Web Interface                 http://127.0.0.1:4040                                                                                                                                 
Forwarding                    https://b99d-177-74-63-178.ngrok-free.app -> http://localhost:8000
`

#### Caso ocorra erro de autenticação:
Se ao executar o comando aparecer a mensagem `authentication failed: Usage of ngrok requires a verified account and authtoken`, siga os passos abaixo:

1. Acesse o site do [ngrok](https://ngrok.com/) e cadastre-se.
2. Copie seu token de autenticação, disponível na seção "Your Authtoken".
3. Configure o token em sua máquina executando o seguinte comando:

```bash
ngrok config add-authtoken $YOUR_AUTHTOKEN
```

Substitua `$YOUR_AUTHTOKEN` pelo token gerado na sua conta do ngrok.




### 4. Verifique os Contêineres

Certifique-se de que todos os contêineres estão em execução:

```bash
docker ps
```

Esse comando lista todos os contêineres ativos.

---

## Acessando o Sistema

Dependendo de como seu sistema está configurado, você pode acessá-lo através do navegador no seguinte endereço:

```
localhost:<porta-configurada>
```

Substitua `<porta-configurada>` pela porta definida no arquivo `docker-compose.yml`. Geralmente é `8000`, ou outra que você configurou.

---

## Finalizando os Contêineres

Quando quiser interromper a execução do projeto, utilize o comando:

```bash
docker compose down
```

Esse comando:

- Para e remove os contêineres criados pelo `docker-compose`.
- Remove as redes criadas pelo projeto.

