<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SentinelarController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        if (isset($data['message']['text'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'];

            switch ($text) {
                case '/start':
                    $this->sendMessage($chatId, $this->getWelcomeMessage());
                    break;
                case '1':
                    $this->sendMessage($chatId, $this->getCreatorInfo());
                    break;
                case '2':
                    $this->sendMessage($chatId, $this->getBotFeatures());
                    break;
                default:
                    $this->sendMessage($chatId, "Não entendi sua escolha. Por favor, envie *1* ou *2* para continuar.");
            }
        }
    }

    private function sendMessage($chatId, $text): void
    {
        $token = '7582105005:AAFYllvqbuCGnPjJKSzxGhIhR9lYXa-YlFM';
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        if (!$response->successful()) {
            Log::error('Erro ao enviar mensagem', ['chat_id' => $chatId, 'response' => $response->json()]);
        } else {
            Log::info('Mensagem enviada com sucesso', ['chat_id' => $chatId, 'text' => $text]);
        }
    }

    private function getWelcomeMessage(): string
    {
        return "Olá, eu sou o *Bot Sentinela*! 👋\n\n"
            . "Gostaria de saber mais sobre mim? Escolha uma das opções abaixo:\n\n"
            . "1️⃣ Saber sobre o meu criador.\n"
            . "2️⃣ Descobrir o que eu posso fazer.\n\n"
            . "Envie o número correspondente à sua escolha.";
    }


    private function getCreatorInfo(): string
{
    return "*🌟 Apresentando meu Criador! 🌟*\n\n"
        . "Olá! Eu sou o resultado do trabalho e dedicação de *Matheus Gonçalves*, um desenvolvedor web talentoso e apaixonado por tecnologia. Ele me criou com o objetivo de tornar tarefas mais fáceis e rápidas para você! 🚀\n\n"
        . "👨‍💻 *Quem é Matheus?*\n"
        . "Um profissional que domina tanto o *front-end* quanto o *back-end*, sempre em busca de inovação e excelência. Atualmente, ele está trabalhando na *Divisão de Desenvolvimento* da *Polícia Civil do Pará*, onde transforma ideias em soluções tecnológicas eficientes e modernas.\n\n"
        . "💼 *O que ele faz atualmente?*\n"
        . "   - Desenvolvimento de sistemas web com tecnologias modernas, como Laravel, Vue.js, Vuetify e PostgreSQL.\n"
        . "   - Criação de bots automatizados que otimizam processos internos.\n"
        . "   - Contêinerização de projetos usando Docker e Docker Compose.\n"
        . "   - Melhorias em sistemas legados para trazer eficiência e modernidade.\n\n"
        . "💻 *Tecnologias que ele domina:*\n"
        . "   - *Front-end:*\n"
        . "      🟦 HTML5 | 🎨 CSS3 | 🔷 Vue.js 3 | 🖌️ Vuetify 3 | 📦 Bootstrap 5\n"
        . "   - *Back-end:*\n"
        . "      🐘 PHP | 🎯 Laravel | 📡 APIs (RESTful) | 🛠️ Padrão MVC\n"
        . "   - *Banco de Dados:*\n"
        . "      🐬 MySQL | 🐘 PostgreSQL\n"
        . "   - *Ferramentas e Tecnologias:*\n"
        . "      🐳 Docker | ⚙️ Docker Compose | 📂 Git | 📜 Composer | ⚡ Node.js | 🌐 Axios\n\n"
        . "📞 *Quer falar com ele? Aqui estão os contatos:*\n"
        . "   - 📞 *Telefone:* +55 91 98539-9924\n"
        . "   - ✉️ *E-mail:* matheus.devcode19@gmail.com\n"
        . "   - 💻 *GitHub:* [github.com/Matheus-DevCode](https://github.com/Matheus-DevCode)\n"
        . "   - 🔗 *LinkedIn:* [linkedin.com/in/matheusgoncalvesps](https://linkedin.com/in/matheusgoncalvesps)\n\n"
        . "*Se precisar de algo, não hesite em perguntar! Estou aqui para ajudar e, claro, para mostrar as incríveis habilidades do meu criador!* ✨😊";
}


    private function getBotFeatures(): string
    {
        return "*Aqui estão 5 coisas que posso fazer por você:*\n\n"
            . "1️⃣ *Monitorar Estoque:* Verifico e alerto sobre níveis de estoque automaticamente.\n"
            . "2️⃣ *Notificações:* Envio alertas e lembretes para mantê-lo informado.\n"
            . "3️⃣ *Relatórios:* Gero relatórios detalhados com base nos dados fornecidos.\n"
            . "4️⃣ *Integrações:* Conecto-me a APIs e sistemas para melhorar processos.\n"
            . "5️⃣ *Suporte Inteligente:* Respondo perguntas e ofereço informações úteis rapidamente.\n\n"
            . "Como posso ajudar você hoje? 😄";
    }

}
