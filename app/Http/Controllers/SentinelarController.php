<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SentinelarController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        $data = $request->all();

        // “Log” completo dos dados recebidos (para depuração inicial)
        Log::info("Dados recebidos no webhook:", ['data' => $data]);

        // Tratamento de mensagens de texto
        if (isset($data['message']['text'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'];

            // Log do texto recebido
            Log::info("Texto recebido:", ['text' => $text]);

            switch ($text) {
                case '/start':
                    $this->sendMessage($chatId, $this->getWelcomeMessage(), $this->getMainMenuButtons(), true);
                    break;
                case 'sobre_criador':
                    $this->sendMessage($chatId, $this->getCreatorInfo());
                    break;
                case 'sobre_bot':
                    $this->sendMessage($chatId, $this->getBotFeatures(), $this->getBotMenuButtons());
                    break;
                case 'monitorar_estoque':
                    $this->sendMessage($chatId, $this->getMonitoramentoEstoque());
                    break;
                case 'notificacoes':
                    $this->sendMessage($chatId, $this->getNotificacoes());
                    break;
                case 'relatorios':
                    $this->sendMessage($chatId, $this->getRelatorios());
                    break;
                case 'integracoes':
                    $this->sendMessage($chatId, $this->getIntegracoes());
                    break;
                case 'suporte':
                    $this->sendMessage($chatId, $this->getSuporte());
                    break;
                default:
                    $this->sendMessage($chatId, "Comando não reconhecido. Por favor, coloque /start para iniciar.", [], true);
            }
        }

        // Tratamento de callbacks
        if (isset($data['callback_query']['data'])) {
            $callbackData = $data['callback_query']['data'];
            $chatId = $data['callback_query']['message']['chat']['id'];

            // Log do callback recebido
            Log::info("Callback recebido:", ['callback_data' => $callbackData]);

            switch ($callbackData) {
                case '/start': // Tratar o callback '/start'
                    $this->sendMessage($chatId, $this->getWelcomeMessage(), $this->getMainMenuButtons(), true);
                    break;
                case 'sobre_criador':
                    $this->sendMessage($chatId, $this->getCreatorInfo());
                    break;
                case 'sobre_bot':
                    $this->sendMessage($chatId, $this->getBotFeatures(), $this->getBotMenuButtons());
                    break;
                case 'monitorar_estoque':
                    $this->sendMessage($chatId, $this->getMonitoramentoEstoque());
                    break;
                case 'notificacoes':
                    $this->sendMessage($chatId, $this->getNotificacoes());
                    break;
                case 'relatorios':
                    $this->sendMessage($chatId, $this->getRelatorios());
                    break;
                case 'integracoes':
                    $this->sendMessage($chatId, $this->getIntegracoes());
                    break;
                case 'suporte':
                    $this->sendMessage($chatId, $this->getSuporte());
                    break;
                default:
                    $this->sendMessage($chatId, "Comando não reconhecido no callback.");
            }
        }
    }

    private function sendMessage($chatId, $text, $buttons = [], $isMainMenu = false): void
    {
        $token = '7582105005:AAFYllvqbuCGnPjJKSzxGhIhR9lYXa-YlFM';
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        if (!$isMainMenu && !in_array('/start', array_column($buttons, 'callback_data'))) {
            $backToMainMenuButton = [
                'text' => 'Voltar ao Menu Principal',
                'callback_data' => '/start'
            ];
            $buttons[] = $backToMainMenuButton; // Adiciona o botão nos menus secundários
        }

        // Criação do teclado
        $keyboard = [];
        if (!empty($buttons)) {
            foreach ($buttons as $button) {
                $keyboard[] = [
                    'text' => $button['text'],
                    'callback_data' => $button['callback_data']
                ];
            }
        }

        // Criando o objeto de botões
        $keyboard = [];

        if (!empty($buttons)) {
            foreach ($buttons as $button) {
                // Assegura que cada botão tenha a chave 'text' e 'callback_data'
                $keyboard[] = [
                    'text' => $button['text'],
                    'callback_data' => $button['callback_data']
                ];
            }
        }

        // Se existirem botões, monta o 'reply_markup', caso contrário, passa um objeto vazio
        $replyMarkup = !empty($keyboard) ? json_encode([
            'inline_keyboard' => array_chunk($keyboard, 1) // Cria a estrutura de teclado de uma coluna
        ]) : json_encode([
            'inline_keyboard' => [] // Passa um teclado vazio, não null
        ]);

        // Envia a mensagem com o teclado inline (se houver)
        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $replyMarkup // Envia o teclado se existir
        ]);

        // Verifica a resposta da API do Telegram
        if (!$response->successful()) {
            Log::error('Erro ao enviar mensagem', [
                'chat_id' => $chatId,
                'response' => $response->json(),
                'reply_markup' => $replyMarkup // Log adicional para verificar a estrutura do teclado
            ]);
        } else {
            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
        }
    }

        private function getMainMenuButtons(): array
        {
            return [
                [
                    'text' => 'Sobre o Criador',
                    'callback_data' => 'sobre_criador'
                ],
                [
                    'text' => 'Sobre o que posso fazer',
                    'callback_data' => 'sobre_bot'
                ]
            ];
        }

    private function getCreatorMenuButtons(): array
    {
        return [
            [
                'text' => 'Voltar ao Menu Principal',
                'callback_data' => '/start'
            ]
        ];
    }

    private function getBotMenuButtons(): array
    {
        return [
            [
                'text' => 'Monitorar Estoque',
                'callback_data' => 'monitorar_estoque'
            ],
            [
                'text' => 'Notificações',
                'callback_data' => 'notificacoes'
            ],
            [
                'text' => 'Relatórios',
                'callback_data' => 'relatorios'
            ],
            [
                'text' => 'Integrações',
                'callback_data' => 'integracoes'
            ],
            [
                'text' => 'Suporte Inteligente',
                'callback_data' => 'suporte'
            ],
            [
                'text' => 'Voltar ao Menu Principal',
                'callback_data' => '/start'
            ]
        ];
    }

    public function getWelcomeMessage(): string
    {
        return "Olá! Bem-vindo ao nosso sistema. Como posso te ajudar hoje?";
    }

    public function getCreatorInfo(): string
    {
        return "🌟 Apresentando meu Criador! 🌟\n\n"
            . "Olá! Eu sou o resultado do trabalho e dedicação de Matheus Gonçalves, um desenvolvedor web talentoso e apaixonado por tecnologia. Ele me criou com o objetivo de tornar tarefas mais fáceis e rápidas para você! 🚀\n\n"
            . "👨‍💻 Quem é Matheus?\n"
            . "Um profissional que domina tanto o front-end quanto o back-end, sempre em busca de inovação e excelência. Atualmente, ele está trabalhando na Divisão de Desenvolvimento da Polícia Civil do Pará, onde transforma ideias em soluções tecnológicas eficientes e modernas.\n\n"
            . "💼 O que ele faz atualmente?\n"
            . "   - Desenvolvimento de sistemas web com tecnologias modernas, como Laravel, Vue.js, Vuetify e PostgreSQL.\n"
            . "   - Criação de bots automatizados que otimizam processos internos.\n"
            . "   - Contêinerização de projetos usando Docker e Docker Compose.\n"
            . "   - Melhorias em sistemas legados para trazer eficiência e modernidade.\n\n"
            . "💻 Tecnologias que ele domina:\n"
            . "   - Front-end:\n"
            . "      🟦 HTML5 | 🎨 CSS3 | 🔷 Vue.js 3 | 🖌️ Vuetify 3 | 📦 Bootstrap 5\n"
            . "   - Back-end:\n"
            . "      🐘 PHP | 🎯 Laravel | 📡 APIs (RESTful) | 🛠️ Padrão MVC\n"
            . "   - Banco de Dados:\n"
            . "      🐬 MySQL | 🐘 PostgreSQL\n"
            . "   - Ferramentas e Tecnologias:\n"
            . "      🐳 Docker | ⚙️ Docker Compose | 📂 Git | 📜 Composer | ⚡ Node.js | 🌐 Axios\n\n"
            . " Quer falar com ele? Aqui estão os contatos:\n"
            . "   - 📞 Telefone: +55 91 98539-9924\n"
            . "   - ✉️ E-mail: matheus.devcode19@gmail.com\n"
            . "   - 💻 GitHub: [github.com/Matheus-DevCode](https://github.com/Matheus-DevCode)\n"
            . "   - 🔗 LinkedIn: [linkedin.com/in/matheusgoncalvesps](https://linkedin.com/in/matheusgoncalvesps)";
    }

    public function getBotFeatures(): string
    {
        return "Eu posso:\n"
            . "1️⃣ Monitorar Estoque\n"
            . "2️⃣ Enviar Notificações\n"
            . "3️⃣ Gerar Relatórios\n"
            . "4️⃣ Integrar com APIs\n"
            . "5️⃣ Oferecer Suporte Inteligente\n\n"
            . "Escolha uma opção para saber mais!";
    }

    public function getMonitoramentoEstoque(): string
    {
        return "📦 **Monitoramento de Estoque em Tempo Real**\n\n"
            . "Aqui está o status atual do seu estoque:\n\n"
            . "1️⃣ **Produto A** - 120 unidades em estoque\n"
            . "2️⃣ **Produto B** - 85 unidades em estoque\n"
            . "3️⃣ **Produto C** - 54 unidades em estoque\n"
            . "4️⃣ **Produto D** - 200 unidades em estoque\n"
            . "5️⃣ **Produto E** - 0 unidades em estoque (⚠️ Precisa de reposição)\n\n"
            . "👀 **Avisos:**\n"
            . "   - **Produto E** está sem estoque, precisa ser reabastecido o mais rápido possível.\n"
            . "   - **Produto A** está com estoque alto, talvez seja necessário revisar o pedido.\n\n"
            . "_Dados atualizados em tempo real._";
    }

    public function getNotificacoes(): string
    {
        return "🔔 **Configuração de Notificações e Alertas**\n\n"
            . "Eu posso te enviar alertas e lembretes personalizados! Aqui estão as opções disponíveis:\n\n"
            . "1️⃣ **Alertas de Estoque Baixo**\n"
            . "   - Receba um alerta quando o estoque de um produto atingir um nível crítico.\n\n"
            . "2️⃣ **Lembretes de Reposição**\n"
            . "   - Defina lembretes para te avisar quando for necessário repor um produto no estoque.\n\n"
            . "3️⃣ **Alertas Diários**\n"
            . "   - Receba um resumo diário do status do seu estoque ou das suas vendas.\n\n"
            . "4️⃣ **Alertas de Preço**\n"
            . "   - Configure alertas para quando os preços dos produtos mudarem ou atingirem um limite que você definir.\n\n"
            . "🔧 **Como configurar?**\n"
            . "   - Escolha a opção desejada e me avise! Posso configurar isso para você e manter você sempre informado.\n\n"
            . "📝 Exemplo de resposta:\n"
            . "   - _Quero receber alertas de estoque baixo._\n"
            . "   - _Me avise quando o preço de um produto mudar._\n\n"
            . "Escolha a opção que deseja configurar ou me diga como posso ajudar!";
    }

    public function getRelatorios(): string
    {
        return "📊 **Relatório de Estoque - Janeiro de 2025** 📊\n\n"
            . "Este é um resumo do estado atual do seu estoque. Este relatório é gerado com base nas informações disponíveis no sistema.\n\n"
            . "### 📦 **Produtos em Estoque**\n\n"
            . "1. **Produto A**\n"
            . "   - Quantidade: 150 unidades\n"
            . "   - Preço Unitário: R$ 10,00\n"
            . "   - Total em Estoque: R$ 1.500,00\n"
            . "   - Localização: Prateleira 1, Aisle 2\n\n"

            . "2. **Produto B**\n"
            . "   - Quantidade: 200 unidades\n"
            . "   - Preço Unitário: R$ 25,00\n"
            . "   - Total em Estoque: R$ 5.000,00\n"
            . "   - Localização: Prateleira 3, Aisle 5\n\n"

            . "3. **Produto C**\n"
            . "   - Quantidade: 50 unidades\n"
            . "   - Preço Unitário: R$ 100,00\n"
            . "   - Total em Estoque: R$ 5.000,00\n"
            . "   - Localização: Prateleira 4, Aisle 3\n\n"

            . "### 🔴 **Produtos com Baixa Quantidade**\n\n"
            . "1. **Produto A** - Estoque abaixo de 30% do nível recomendado. Aconselha-se reabastecer o estoque.\n"
            . "   - Quantidade recomendada: 500 unidades\n\n"

            . "### 🛒 **Pedidos Realizados**\n\n"
            . "1. **Pedido 1001**\n"
            . "   - Produto A: 100 unidades\n"
            . "   - Produto B: 50 unidades\n"
            . "   - Status: Enviado\n"
            . "   - Previsão de chegada: 10 de Janeiro de 2025\n\n"

            . "2. **Pedido 1002**\n"
            . "   - Produto C: 30 unidades\n"
            . "   - Produto A: 50 unidades\n"
            . "   - Status: Aguardando envio\n\n"

            . "### 📅 **Resumo Mensal**\n\n"
            . "1. **Vendas de Janeiro**\n"
            . "   - Total de vendas: R$ 25.000,00\n"
            . "   - Produtos mais vendidos: Produto A (500 unidades)\n\n"

            . "2. **Compras de Janeiro**\n"
            . "   - Total de compras: R$ 18.000,00\n"
            . "   - Fornecedor: ABC Distribuidora\n\n"

            . "### ⚠️ **Avisos e Alertas**\n\n"
            . "1. **Produto A** - Estoque baixo, reabastecer em até 5 dias.\n"
            . "2. **Produto B** - Pedido de reabastecimento em andamento.\n\n"

            . "*Este relatório é uma simulação e não reflete dados reais. Para mais informações, entre em contato com o suporte.*";

    }

    public function getIntegracoes(): string
    {
        return "Posso me conectar a várias APIs para otimizar processos. Qual integração você deseja?";
    }

    public function getSuporte(): string
    {
        return "🤖 **Suporte Inteligente** 🤖\n\n"
            . "Olá! Estou aqui para te ajudar com qualquer dúvida ou problema que você tenha. Veja algumas opções de como posso te apoiar:\n\n"
            . "### 💡 **Dúvidas Frequentes**\n"
            . "1. **Como posso monitorar o estoque?**\n"
            . "   - Eu posso te ajudar a visualizar seu estoque em tempo real. Basta me pedir para monitorar o estoque!\n\n"
            . "2. **Quais são as funcionalidades do bot?**\n"
            . "   - Posso te ajudar a gerar relatórios, configurar notificações, integrar com APIs e muito mais!\n\n"
            . "### 🔧 **Como posso te ajudar?**\n"
            . "1. **Problemas técnicos?**\n"
            . "   - Se você está enfrentando problemas técnicos, descreva a situação e farei o possível para encontrar uma solução.\n\n"
            . "2. **Fale diretamente com o suporte humano!**\n"
            . "   - Caso você precise de ajuda mais avançada, posso te direcionar para nosso time de suporte especializado.\n\n"
            . "### 📞 **Informações de Contato**\n"
            . "Se preferir, você também pode entrar em contato conosco diretamente através dos seguintes canais:\n\n"
            . "📧 **E-mail:** suporte@empresa.com\n"
            . "📱 **WhatsApp:** +55 91 99999-9999\n"
            . "💬 **Chat Online:** Acesse nosso chat no site para suporte em tempo real.\n\n"
            . "Estou sempre disponível para te ajudar, é só me chamar! 😊";

    }
}
