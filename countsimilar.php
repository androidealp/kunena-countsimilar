<?php
/**
 * @package     Kunena.Plugin
 * @subpackage  countsimilar
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author 		André Luiz Pereira <[<andre@next4.com.br>]>
 */

defined('_JEXEC') or die ();

class plgKunenaCountsimilar extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		// Do not load if Kunena version is not supported or Kunena is offline
		if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('4.0') && KunenaForum::installed()))
		{
			return;
		}

		parent::__construct($subject, $config);

		$this->loadLanguage('plg_kunena_countsimilar.sys', JPATH_ADMINISTRATOR) || $this->loadLanguage('plg_kunena_countsimilar.sys', KPATH_ADMIN);
	}

	/**
	* pega o trigger para topic.action
	*/
	public function onKunenaGetButtons($scenario, $bts, $obj) //'topic.action', $this->topicButtons, $this
	{

			//$bts->def('testebt', $obj->getButton('#', 'indented', 'layout', 'user'));
			if($scenario == 'topic.action'){
				$countsames = $this->countSames($obj->topic->id);
				$count = 0;
				$list = array();
				if($countsames){
					$count = $countsames['count'];
					$list = $countsames['list'];
				}

				//SE o usuário estiver logado
				if($obj->me->userid){

					$userinlist = $this->checkUser($obj->me->userid, $list);
					if($userinlist){
						$bts->def('mesmoproblema', '<a class="kicon-button kbuttonuser btn-left" href="#"><span class="layout-mesmoproblema">'.JText::_('PLG_KUNENA_COUNTSIMILAR_ERRORUM').'</span></a>');	
					}else{
						$bts->def('mesmoproblema', '<a data-kunemacheck="'.$obj->topic->id.'" class="kicon-button kbuttonuser btn-left" href="#"><span class="layout-mesmoproblema">'.JText::_('PLG_KUNENA_COUNTSIMILAR_MESMOPROBLEMA').'</span></a>');		
					}
					
				}

				$bts->def('countmesmoproblema', '<small class="similar-smallinfo"><span class="countnum">'.$count.'</span>'.JText::_('PLG_KUNENA_COUNTSIMILAR_COUNTMESMOPROBLEMA').'</small>');	
				
				
			}

	}

	/**
	 * Check user and count
	 * @param  int $id topic id
	 * @return mixer return list and count or int 0 if not result
	 */
	private function countSames($id){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
            ->select($db->quoteName('userid'))
            ->from($db->quoteName('#__kunena_sameproblem'))
            ->where('topic_id = '.(int)$id);
			$db->setQuery($query);
			$db->execute();
			$num_rows = $db->getNumRows();
			$list = $db->loadObjectList();
			return ($num_rows)?array('count'=>$num_rows, 'list'=>$list):0;
	}

	/**
	 * @param  int $user_id get user
	 * @param  array $list get list error
	 * @return bollean return if user exist in list
	 */
	private function checkUser($user_id, $list){
		$return = false;

			foreach ($list as $r => $user) {
				if($user->userid == $user_id){
					$return = true; 
				}
			}
		
		return $return;
	}


	public function onKunenaBeforeRender()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		if($app->isAdmin()) {
		    return;
		}

		$idPost = $app->input->post->get('inserir',0,'INT');
		$option = $app->input->get('option',0,'STRING');
		$view = $app->input->get('view',0,'STRING');



		if($option == 'com_kunena' && $view == 'topic'){

			$user = JFactory::getUser();

			if (!$user->guest) {

			$script = <<<HTML

			jQuery(function($){
			$('[data-kunemacheck]').one('click',function(e){
					e.preventDefault();
					var obj = $(this);
					var kcheck = obj.data('kunemacheck');
					var kurl =$(location).attr('href');

					obj.find('span.layout-mesmoproblema').text('...');
						$.post( kurl, {inserir:kcheck},function(resultados, status){
							
							if(resultados.length < 150){
								obj.find('span.layout-mesmoproblema').text(resultados);
							}else{
								obj.text("error");
							}
							
						});
					
				});
			});

HTML;

			$doc->addScriptDeclaration($script);
		} //check se está logado

		}

		if($idPost){
			$mensagens = 'fazio';
			$listsames = $this->countSames($idPost);

			$user = JFactory::getUser();

			if (!$user->guest) {
				$userid = $user->id;

				$mensagens = $this->aplicarBD($userid, $listsames['list'], $idPost);

			}else{
				$mensagens = JText::_('PLG_KUNENA_COUNTSIMILAR_ERRORTRES');
			}

			//$doc->setMimeEncoding('application/json');
			//	JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');
			//	echo json_encode($mensagens);
			
			echo $mensagens;
			$app->close();

		}

	}

	/**
	 * prepara os dados para salvar no banco
	 * @param  int $userid id do usuário
	 * @param  mixer $list   lista de usuários que marcou, ou false
	 * @param  int $idPost id do topco
	 * @return string         Retorna o texto da ação
	 */
	private function aplicarBD($userid , $list, $idPost){
		$mensagem = '';
		$executeDB = 0;
	
		//existe uma lista neste post
		if($list){
			$userinlist = $this->checkUser($userid, $list);

			if($userinlist){
				$mensagem = JText::_('PLG_KUNENA_COUNTSIMILAR_ERRORUM'); 
			}else{
				$executeDB = 1;
			}
		}else{
			$executeDB = 1;
		}
		// o usuário não está na lista adiciona o usuário
		if($executeDB){
			//insere na tabela __kunena_sameproblem
		 $mensagem =	$this->insertTable($idPost, $userid);
		}

		return $mensagem;
	}


	/**
	 * Cria uma row na tabela
	 * @param  int $idPost Id do topico
	 * @param  int $userid Id do usuário
	 * @return [string]         Retorna que foi salvo com sucesso
	 */
	private function insertTable($idPost, $userid){

		$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$columns = array('topic_id', 'userid');

			$values = array((int)$idPost, (int)$userid);

			$query
			    ->insert($db->quoteName('#__kunena_sameproblem'))
			    ->columns($db->quoteName($columns))
			    ->values(implode(',', $values));
			    $db->setQuery($query);
				$db->execute();
				//retorna se a execução foi um sucesso
				return JText::_('PLG_KUNENA_COUNTSIMILAR_SUCCESS');
	}

}
