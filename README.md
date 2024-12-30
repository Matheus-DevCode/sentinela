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
                                                                                                                                                                                                                                                        
```
Sign up to try new private endpoints https://ngrok.com/new-features-update?ref=private                                                                                            
                                                                                                                                                                                    
Session Status                online                                                                                                                                                
Account                       Matehus-Dev (Plan: Free)                                                                                                                              
Update                        update available (version 3.19.0, Ctrl-U to update)                                                                                                   
Version                       3.18.4                                                                                                                                                
Region                        South America (sa)                                                                                                                                    
Latency                       66ms                                                                                                                                                  
Web Interface                 http://127.0.0.1:4040                                                                                                                                 
Forwarding                    https://b99d-177-74-63-178.ngrok-free.app -> http://localhost:8000
```

pegue o (Forwarding) `https://b99d-177-74-63-178.ngrok-free.app`.

Abra outrp terminal não feche o terminal que está expondo sua aplicação e no noo terminal coloque o seguinte comando 

```bash
curl -X POST "https://api.telegram.org/bot7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc/setWebhook?url=Forwarding/telegram-webhook"
```
Preucure no comando a palavra `( Forwarding )` e substitua pelo valor de dele `https://b99d-177-74-63-178.ngrok-free.app` sempre que aplicação for derrubada e levantada precisar ser feito isso pois esse valor muda

Exemplo
```
curl -X POST "https://api.telegram.org/bot7815578518:AAH7D5woM4L-LQXfZgCN21TbPsJ_WPPT_kc/setWebhook?url=https://b99d-177-74-63-178.ngrok-free.app/telegram-webhook"
```


### 4. Acesse o bot

ACESSE ESSE LINK E MANDE UMA MENSAGEM COM `/start` para iniciar uma conversa com o bot 
LINK: 
```bash
https://t.me/Mdev_teste_Bot
```

