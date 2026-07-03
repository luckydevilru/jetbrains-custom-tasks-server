<?php

namespace App\Controller;

use App\Service\EvaProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['hi']);
    }
        // Роут для списка задач
    #[Route('/eva/tasks', name: 'api_tasks_list', methods: ['GET'])]
    public function list(Request $request, EvaProjectService $evaService): JsonResponse
    {
        $query = $request->query->get('query', '');
        $data = $evaService->getTasksList($query);
        return $this->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    // Роут для получения деталей одной конкретной задачи
    #[Route('/eva/tasks/{id}', name: 'api_tasks_single', methods: ['GET'])]
    public function single(string $id, EvaProjectService $evaService): JsonResponse
    {
        $data = $evaService->getSingleTask($id);
        return $this->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
