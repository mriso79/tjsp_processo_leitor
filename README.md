# tjsp_processo_leitor em PHP
Um parser dos processos no tribunal de Justiça de SP. Basta informar o número do processo, e esta pequena classe PHP lhe retornará um JSON com as informações desejadas do processo.

# MANUTENÇÃO DO DATACENTER
*O portal do TJSP e os serviços on-line estarão indisponíveis de 2 a 9 de janeiro, em razão da manutenção no datacenter pela Secretaria de Tecnologia da Informação.*


**composer require mriso_dev/tjsp_processo_leitor**

Adicione *"minimum-stability":"dev"* no composer.json

include 'vendor/autoload.php';
use TJSPWebService\TJSPWebService;
TJSPWebService::ProcessoToJson('xxxxxxx.xxxxx.x.xxx.xxx');

Na pasta functional temos uma script funcional para os mais simplistas.

Obs: Necessita da classe DOM habilitada.

É apenas uma ferramenta para auxiliar quem está tendo problemas em fazê-lo. Este snippet é não-oficial, está disponibilizado "tal como está" e não damos suporte ou garantia alguma.

Espero que lhes seja útil.
