<?php

/**
 * Class TJSPWebService
 * Use o método getProcesso se vc quiser que retorne os dados
 * Use o método ProcessoToJson se vc quiser que retorne um JSON para sua aplicação
 */
namespace TJSPWebService;

class TJSPWebService
{
    public static function getProcesso($idProcesso, $json = false) {
        $base_url = 'http://esaj.tjsp.jus.br/cpopg/search.do?';
        $base_url.= 'dadosConsulta.localPesquisa.cdLocal=-1';
        $base_url.= '&cbPesquisa=NUMPROC';
        $base_url.= '&dadosConsulta.valorConsulta=';

        try {
            $html = file_get_contents($base_url.$idProcesso);
        }catch(\Exception $e){
            $msg = 'Não conseguimos conectar com o site do TJSP. Tente novamente mais tarde.';
            $var = ['erro'=>$msg];
            if($json) {
                $json = json_encode(['erro' => $msg]);
                echo $json;
                exit;
            }
            return $var;
        }

        $html = preg_replace('/^\s+|\n|\r|\s+$/m', '', $html);
        $doc = new \DOMDocument();
        @$doc->loadHtml($html);

        $data = [];
        $read = 0;

        $warning = null;
        $warning = @$doc->getElementById('mensagemRetorno');
        if($warning != null){
            $msg = $warning->firstChild->nodeValue;
            $var = ['erro'=>$msg];

            if($json) {
                $json = json_encode(['erro' => $msg]);
                echo $json;
                exit;
            }

            return $var;
        }

        $data['MOV'] = [];

        $finder = new \DomXPath($doc);
        $classname="secaoFormBody";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $nodePro = [];
        foreach($nodes as $node) {
            $nodePro[] = $node;
        }

        if(isset($nodePro[1])) {
            $dados_processo = $nodePro[1];
        }else{
            $msg = 'Erro ao parsear este processo. Verifique no site do TJSP.';
            $var = ['erro'=>$msg];
            if($json) {
                $json = json_encode(['erro' => $msg]);
                echo $json;
                exit;
            }
            return $var;
        }

        $cc = 0;
        foreach($dados_processo->childNodes as $dpnode) {
            if($cc == 0){
                $cc++;
                continue;
            }
            $headerR = rtrim($dpnode->firstChild->nodeValue, ' ');
            $headerR = rtrim($headerR, ':');
            $data['DPS'][($headerR)?$headerR:'Info'] = trim($dpnode->lastChild->nodeValue);
        }

        $tblpartes = @$doc->getElementById('tablePartesPrincipais');
        foreach (@$tblpartes->childNodes as $t) {
            $nodes = [];
            foreach($t->childNodes as $node) {
                $nodes[] = $node;
            }

            if($nodes[0]->nodeValue == 'Exectdo: '){
                $rheader = 'Executado';
                $C = 0;
                foreach($nodes[1]->childNodes as $chn){
                    if($C == 0){
                        $C++;
                        continue;
                    }
                    if($chn->nodeValue != '') {
                        if(isset($chn->wholeText)) {
                            $data['PRT']['Advogados'][] = trim($chn->nodeValue);
                        }
                    }
                }
            }else{
                $rheader = 'Exequente';
            }

            $data['PRT'][$rheader] = $t->lastChild->firstChild->nodeValue;
        }

        $tblmovimentacoes = @$doc->getElementById('tabelaTodasMovimentacoes');
        foreach (@$tblmovimentacoes->childNodes as $t) {
            $data['MOV'][] = [$t->firstChild->nodeValue,$t->lastChild->firstChild->nodeValue, 'TJSP'];
        }

        $tblcdas = @$doc->getElementById('tableCdasPrincipais');
        $header = [];
        $childrens = [];
        $c = 0;
        foreach (@$tblcdas->childNodes as $t) {

            foreach($t->firstChild->childNodes as $nodes) {
                if($nodes->tagName == 'th' && $c == 0){
                    $header[] = [$nodes->nodeValue];
                }
            }
            $c++;

            if($t->tagName == 'tbody'){
                foreach($t->childNodes as $n) {
                    $x = 0;
                    foreach($n->childNodes as $nn){
                        $childrens[$header[$x][0]] = $nn->nodeValue;
                        $x++;
                    }
                }
                $data['CDA'][] = $childrens;
            }

        }

        return $data;
    }

    public static function ProcessoToJson($idProcesso){
        header('Content-Type: application/json');
        $data = self::getProcesso($idProcesso, true);
        $json = json_encode($data);
        echo $json;
    }
}