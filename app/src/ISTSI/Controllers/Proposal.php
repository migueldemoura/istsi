<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Identifiers\Notice;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Proposal
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function getList(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');

        $proposals = array_column($proposalMapper->where(
            ['company_id' => $companyMapper->first(['email' => $uid])->id]
        )->toArray(), 'id');

        return $response->withJson([
            'status' => 'success',
            'data'   => [
                'proposals' => $proposals
            ]
        ]);
    }

    public function getData(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');

        $proposal = $args['proposal'];

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');

        $data = $proposalMapper->get($proposal);
        if (!$data) {
            die('E_INVALID_PROPOSAL');
        }

        return $response->withJson([
            'status' => 'success',
            'data'   => [
                'id' => $data->id,
                'company' => $companyMapper->get($data->company_id)->name,
                'description' => $data->description,
                'project' => $data->project,
                'requirements' => $data->requirements,
                'salary' => $data->salary,
                'observations' => $data->observations,
                'duration' => $data->duration,
                'location' => $data->location,
                'vacancies' => $data->vacancies,
                'courses' => $data->courses
            ]
        ]);
    }

    public function create(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');

        $proposal = $proposalMapper->build([
            'company_id' => $companyMapper->first(['email' => $uid])->id,
            'description' => $request->getParsedBodyParam('description'),
            'project' => $request->getParsedBodyParam('project'),
            'requirements' => $request->getParsedBodyParam('requirements'),
            'salary' => $request->getParsedBodyParam('salary'),
            'observations' => $request->getParsedBodyParam('observations'),
            'duration' => $request->getParsedBodyParam('duration'),
            'location' => $request->getParsedBodyParam('location'),
            'vacancies' => $request->getParsedBodyParam('vacancies'),
            'courses' => $request->getParsedBodyParam('courses')
        ]);
        if (!$proposalMapper->save($proposal)) {
            throw new \Exception(Notice::PROPOSAL_INVALID);
        }

        $logger->addRecord(Info::PROPOSAL_NEW, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function update(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');

        $proposal = $args['proposal'];

        $proposal = $proposalMapper->first([
            'id' => $proposal,
            'company_id' => $companyMapper->first(['email' => $uid])->id
        ]);

        $proposal->description = $request->getParsedBodyParam('description');
        $proposal->project = $request->getParsedBodyParam('project');
        $proposal->requirements = $request->getParsedBodyParam('requirements');
        $proposal->salary = $request->getParsedBodyParam('salary');
        $proposal->observations = $request->getParsedBodyParam('observations');
        $proposal->duration = $request->getParsedBodyParam('duration');
        $proposal->location = $request->getParsedBodyParam('location');
        $proposal->vacancies = $request->getParsedBodyParam('vacancies');
        $proposal->courses = $request->getParsedBodyParam('courses');
        if (!$proposalMapper->update($proposal)) {
            throw new \Exception(Notice::PROPOSAL_INVALID);
        };

        $logger->addRecord(Info::PROPOSAL_EDIT, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');

        $proposal = $args['proposal'];

        if (!$proposalMapper->delete([
            'id' => $proposal,
            'company_id' => $companyMapper->first(['email' => $uid])->id
        ])) {
            throw new \Exception(Notice::PROPOSAL_INVALID);
        }

        $logger->addRecord(Info::PROPOSAL_DELETE, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }
}
