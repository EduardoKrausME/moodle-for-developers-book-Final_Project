<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Brazilian Portuguese language strings for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Checkpoint';
$string['pluginadministration'] = 'Administração do Checkpoint';
$string['modulename'] = 'Checkpoint';
$string['modulenameplural'] = 'Checkpoints';
$string['modulename_help'] = 'Use um checkpoint quando o estudante precisar enviar uma evidência curta, ' .
    'opcionalmente com um arquivo, para avaliação do professor.';
$string['checkpointname'] = 'Nome do checkpoint';
$string['checkpoint:addinstance'] = 'Adicionar um novo checkpoint';
$string['checkpoint:view'] = 'Visualizar checkpoint';
$string['checkpoint:submit'] = 'Enviar evidência do checkpoint';
$string['checkpoint:grade'] = 'Avaliar envios do checkpoint';
$string['checkpoint:manage'] = 'Gerenciar envios do checkpoint';

$string['allowtext'] = 'Permitir evidência em texto';
$string['allowfile'] = 'Permitir um arquivo de evidência';
$string['duedate'] = 'Data limite';
$string['duedatevalue'] = 'Data limite: {$a}';
$string['maxbytes'] = 'Tamanho máximo do arquivo de evidência';
$string['maxbytes_desc'] = 'Limite global para um arquivo de evidência do checkpoint. ' .
    'Os limites do curso e do site continuam sendo aplicados.';
$string['notifygrade'] = 'Notificar estudantes após a avaliação';
$string['notifygrade_desc'] = 'Coloca na fila uma notificação assíncrona do Moodle quando um envio do checkpoint é avaliado.';

$string['submissiontext'] = 'Evidência em texto';
$string['evidence'] = 'Arquivo de evidência';
$string['editsubmission'] = 'Editar envio';
$string['savesubmission'] = 'Salvar envio';
$string['submissionsaved'] = 'Envio do checkpoint salvo.';
$string['submissionreopened'] = 'O envio foi reaberto.';
$string['submissions'] = 'Envios';
$string['viewsubmissions'] = 'Visualizar envios';
$string['submissionstatus'] = 'Status do envio';
$string['pendingsubmissions'] = 'Envios pendentes';
$string['student'] = 'Estudante';
$string['filterbystatus'] = 'Filtrar por status';
$string['summarycounts'] = 'Enviados: {$a->submitted} · Avaliados: {$a->graded} · ' .
    'Reabertos: {$a->reopened} · Atrasados: {$a->late}';

$string['status:none'] = 'Não enviado';
$string['status:draft'] = 'Rascunho';
$string['status:submitted'] = 'Enviado';
$string['status:graded'] = 'Avaliado';
$string['status:reopened'] = 'Reaberto';
$string['late'] = 'Atrasado';
$string['ontime'] = 'No prazo';
$string['deadline'] = 'Situação do prazo';

$string['grade'] = 'Nota';
$string['gradeverb'] = 'Avaliar';
$string['feedback'] = 'Feedback';
$string['savegrade'] = 'Salvar avaliação';
$string['gradesaved'] = 'Nota e feedback salvos.';
$string['gradefor'] = 'Avaliar envio de {$a}';
$string['graderange'] = 'Faixa válida: 0 a {$a}';
$string['reopen'] = 'Reabrir';

$string['completiondetail:submit'] = 'O estudante deve enviar uma evidência';
$string['completiondetail:grade'] = 'O envio deve ser avaliado';

$string['error:nosubmissiontype'] = 'Habilite evidência em texto, arquivo ou ambos.';
$string['error:emptysubmission'] = 'Adicione uma evidência em texto ou um arquivo antes de enviar.';
$string['error:gradedlocked'] = 'Este envio já foi avaliado. Um professor precisa reabri-lo antes de uma nova edição.';
$string['error:cannotothersubmit'] = 'Você não pode enviar evidência em nome de outro usuário.';
$string['error:invalidgrader'] = 'O avaliador não corresponde ao usuário autenticado.';
$string['error:graderange'] = 'A nota deve estar entre 0 e {$a}.';
$string['error:invalidsubmission'] = 'O envio solicitado não pertence a este checkpoint.';

$string['eventsubmissioncreated'] = 'Envio do checkpoint criado ou atualizado';
$string['eventsubmissiongraded'] = 'Envio do checkpoint avaliado';

$string['messageprovider:graded'] = 'Notificações de avaliação do checkpoint';
$string['notification:subject'] = '{$a} foi avaliado';
$string['notification:body'] = 'Seu checkpoint "{$a->checkpoint}" foi avaliado: {$a->grade} / {$a->maxgrade}.';
$string['notification:small'] = 'Seu checkpoint foi avaliado.';

$string['privacy:metadata:submission'] = 'O envio atual do checkpoint, dados da avaliação e registros de data e hora.';
$string['privacy:metadata:submission:checkpointid'] = 'O checkpoint que recebeu o envio.';
$string['privacy:metadata:submission:userid'] = 'O usuário proprietário do envio.';
$string['privacy:metadata:submission:status'] = 'O estado atual do envio.';
$string['privacy:metadata:submission:text'] = 'A evidência em texto enviada pelo usuário.';
$string['privacy:metadata:submission:grade'] = 'A nota atribuída ao envio.';
$string['privacy:metadata:submission:feedback'] = 'O feedback do professor para o envio.';
$string['privacy:metadata:submission:graderid'] = 'O usuário que avaliou o envio.';
$string['privacy:metadata:submission:timecreated'] = 'Quando o envio foi criado.';
$string['privacy:metadata:submission:timemodified'] = 'Quando o envio foi alterado pela última vez.';
$string['privacy:metadata:submission:timegraded'] = 'Quando o envio foi avaliado.';
$string['privacy:metadata:files'] = 'Arquivos de evidência enviados pelos estudantes são armazenados pela Files API do Moodle.';
$string['privacy:path:submission'] = 'Envio';
$string['privacy:path:grading'] = 'Atividade de avaliação';
