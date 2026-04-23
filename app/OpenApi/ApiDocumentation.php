<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'DataLoop API', version: '1.0.0', description: 'Documentation API DataLoop pour le hackathon')]
#[OA\Server(url: '/', description: 'Serveur local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Token Sanctum: Bearer {token}'
)]
class ApiDocumentation
{
    #[OA\Post(
        path: '/api/v1/auth/register',
        tags: ['Auth'],
        summary: 'Inscription',
        description: 'Format recommande: name, telephone, email, password. Compatibilite legacy: nom et mot_de_passe aussi acceptes.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['telephone', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Kouadio Yao'),
                    new OA\Property(property: 'telephone', type: 'string', maxLength: 20, example: '+2250700000000'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'MotDePasse123!'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Inscription reussie',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Inscription reussie.'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'access_token', type: 'string', example: '1|abc123token'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Kouadio Yao'),
                                new OA\Property(property: 'telephone', type: 'string', example: '+2250700000000'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'contributeur'),
                                new OA\Property(property: 'statut', type: 'string', example: 'actif'),
                                new OA\Property(property: 'score_confiance', type: 'number', example: 0),
                                new OA\Property(property: 'solde_virtuel', type: 'number', example: 0),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-22T09:30:00.000000Z'),
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function authRegister(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        tags: ['Auth'],
        summary: 'Connexion',
        description: 'Format recommande: telephone + password. Compatibilite legacy: mot_de_passe aussi accepte.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['telephone', 'password'],
                properties: [
                    new OA\Property(property: 'telephone', type: 'string', maxLength: 20, example: '+2250700000000'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'MotDePasse123!'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion reussie',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Connexion reussie.'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'access_token', type: 'string', example: '1|abc123token'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Kouadio Yao'),
                                new OA\Property(property: 'telephone', type: 'string', example: '+2250700000000'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'contributeur'),
                                new OA\Property(property: 'statut', type: 'string', example: 'actif'),
                                new OA\Property(property: 'score_confiance', type: 'number', example: 0),
                                new OA\Property(property: 'solde_virtuel', type: 'number', example: 0),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-22T09:30:00.000000Z'),
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function authLogin(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/otp/send',
        tags: ['Auth'],
        summary: 'Envoyer OTP',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP envoye',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'OTP genere et logge pour le mode hackathon.'),
                        new OA\Property(property: 'otp_debug', type: 'string', nullable: true, example: '123456'),
                    ],
                    example: [
                        'message' => 'OTP genere et logge pour le mode hackathon.',
                        'otp_debug' => '123456',
                    ]
                )
            ),
        ]
    )]
    public function authOtpSend(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/otp/verify',
        tags: ['Auth'],
        summary: 'Verifier OTP',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP verifie',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'OTP verifie avec succes.'),
                        new OA\Property(property: 'token_type', type: 'string', nullable: true, example: 'Bearer'),
                        new OA\Property(property: 'access_token', type: 'string', nullable: true, example: '1|abc123token'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Kouadio Yao'),
                                new OA\Property(property: 'telephone', type: 'string', example: '+2250700000000'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'contributeur'),
                                new OA\Property(property: 'statut', type: 'string', example: 'actif'),
                                new OA\Property(property: 'score_confiance', type: 'number', example: 0),
                                new OA\Property(property: 'solde_virtuel', type: 'number', example: 0),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-22T09:30:00.000000Z'),
                            ]
                        ),
                    ],
                    example: [
                        'message' => 'OTP verifie avec succes.',
                        'token_type' => 'Bearer',
                        'access_token' => '1|abc123token',
                        'user' => [
                            'id' => 1,
                            'name' => 'Kouadio Yao',
                            'telephone' => '+2250700000000',
                            'email' => 'user@example.com',
                            'role' => 'contributeur',
                            'statut' => 'actif',
                            'score_confiance' => 0,
                            'solde_virtuel' => 0,
                            'created_at' => '2026-04-22T09:30:00.000000Z',
                        ],
                    ]
                )
            ),
        ]
    )]
    public function authOtpVerify(): void
    {
    }

    #[OA\Post(path: '/api/v1/auth/logout', tags: ['Auth'], summary: 'Deconnexion', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Deconnecte', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Deconnexion reussie.')]))])]
    public function authLogout(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        tags: ['Auth'],
        summary: 'Profil connecte',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Profil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'user', type: 'object', properties: [new OA\Property(property: 'id', type: 'integer', example: 1), new OA\Property(property: 'name', type: 'string', example: 'Kouadio Yao'), new OA\Property(property: 'telephone', type: 'string', example: '+2250700000000'), new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'), new OA\Property(property: 'role', type: 'string', example: 'contributeur'), new OA\Property(property: 'statut', type: 'string', example: 'actif'), new OA\Property(property: 'score_confiance', type: 'number', example: 0), new OA\Property(property: 'solde_virtuel', type: 'number', example: 0), new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-22T09:30:00.000000Z')])], example: ['user' => ['id' => 1, 'name' => 'Kouadio Yao', 'telephone' => '+2250700000000', 'email' => 'user@example.com', 'role' => 'contributeur', 'statut' => 'actif', 'score_confiance' => 0, 'solde_virtuel' => 0, 'created_at' => '2026-04-22T09:30:00.000000Z']]))])
    ]
    public function authMe(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/tasks/next',
        tags: ['Tasks'],
        summary: 'Prochaine tache',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'count', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Tache recuperee avec succes.'),
                        new OA\Property(
                            property: 'task',
                            type: 'object',
                            nullable: true,
                            example: [
                                'id' => 12,
                                'type_tache' => 'classification_image',
                                'question' => 'Que montre cette image ?',
                                'options_reponse' => ['chat', 'chien', 'voiture'],
                                'nb_annotations_requises' => 3,
                                'statut' => 'nouvelle',
                                'is_sentinelle' => false,
                                'image' => [
                                    'id' => 42,
                                    'url_stockage' => 'images/task-42.jpg',
                                    'url' => 'http://localhost/storage/images/task-42.jpg',
                                    'categorie' => 'animaux',
                                ],
                                'annotations_count' => 1,
                                'created_at' => '2026-04-22T09:30:00.000000Z',
                            ]
                        ),
                        new OA\Property(
                            property: 'tasks',
                            type: 'array',
                            nullable: true,
                            items: new OA\Items(
                                type: 'object',
                                example: [
                                    'id' => 12,
                                    'type_tache' => 'classification_image',
                                    'question' => 'Que montre cette image ?',
                                    'options_reponse' => ['chat', 'chien', 'voiture'],
                                    'nb_annotations_requises' => 3,
                                    'statut' => 'nouvelle',
                                    'is_sentinelle' => false,
                                    'image' => [
                                        'id' => 42,
                                        'url_stockage' => 'images/task-42.jpg',
                                        'url' => 'http://localhost/storage/images/task-42.jpg',
                                        'categorie' => 'animaux',
                                    ],
                                    'annotations_count' => 1,
                                    'created_at' => '2026-04-22T09:30:00.000000Z',
                                ]
                            )
                        ),
                    ]
                )
            )
        ]
    )]
    public function tasksNext(): void
    {
    }





    #[OA\Get(
        path: '/api/v1/tasks',
        tags: ['Tasks'],
        summary: 'Lister toutes les taches (pagine)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['nouvelle', 'en_cours', 'terminee'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste paginee des taches',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 12),
                                    new OA\Property(property: 'type_tache', type: 'string', example: 'classification_image'),
                                    new OA\Property(property: 'question', type: 'string', example: 'Que montre cette image ?'),
                                    new OA\Property(property: 'options_reponse', type: 'array', items: new OA\Items(type: 'string'), example: ['chat', 'chien', 'voiture']),
                                    new OA\Property(property: 'nb_annotations_requises', type: 'integer', example: 3),
                                    new OA\Property(property: 'statut', type: 'string', example: 'nouvelle'),
                                    new OA\Property(property: 'is_sentinelle', type: 'boolean', example: false),
                                    new OA\Property(
                                        property: 'image',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', nullable: true, example: 42),
                                            new OA\Property(property: 'url_stockage', type: 'string', nullable: true, example: 'images/task-42.jpg'),
                                            new OA\Property(property: 'categorie', type: 'string', nullable: true, example: 'animaux'),
                                        ]
                                    ),
                                    new OA\Property(property: 'annotations_count', type: 'integer', example: 1),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-22T09:30:00.000000Z'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', nullable: true, example: 'http://localhost:8000/api/v1/tasks?page=1'),
                                new OA\Property(property: 'last', type: 'string', nullable: true, example: 'http://localhost:8000/api/v1/tasks?page=5'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next', type: 'string', nullable: true, example: 'http://localhost:8000/api/v1/tasks?page=2'),
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'path', type: 'string', example: 'http://localhost:8000/api/v1/tasks'),
                                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                new OA\Property(property: 'to', type: 'integer', nullable: true, example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 96),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function tasksIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/tasks/{id}/annotate',
        tags: ['Tasks'],
        summary: 'Soumettre annotation',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reponse_choisie', 'temps_execution_ms'],
                properties: [
                    new OA\Property(property: 'reponse_choisie', type: 'string', maxLength: 255, example: 'chat'),
                    new OA\Property(property: 'temps_execution_ms', type: 'integer', minimum: 0, example: 1850),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Cree', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Annotation enregistree.'), new OA\Property(property: 'consensus_reached', type: 'boolean', example: false), new OA\Property(property: 'annotation', type: 'object', example: ['id' => 98, 'tache_id' => 12, 'utilisateur_id' => 5, 'reponse_choisie' => 'chat', 'temps_execution_ms' => 1850, 'transaction' => ['id' => 501, 'type' => 'gain', 'libelle' => 'Recompense annotation', 'montant' => 250, 'solde_avant' => 1250, 'solde_apres' => 1500, 'reference_tache' => 'task:12'], 'tache' => ['id' => 12, 'type_tache' => 'classification_image', 'question' => 'Que montre cette image ?', 'options_reponse' => ['chat', 'chien', 'voiture'], 'nb_annotations_requises' => 3, 'statut' => 'en_cours', 'is_sentinelle' => false, 'image' => ['id' => 42, 'url_stockage' => 'images/task-42.jpg', 'url' => 'http://localhost/storage/images/task-42.jpg', 'categorie' => 'animaux'], 'annotations_count' => 2, 'created_at' => '2026-04-22T09:30:00.000000Z'], 'created_at' => '2026-04-23T10:00:00.000000Z'])]))]
    )]
    public function tasksAnnotate(): void
    {
    }

    #[OA\Post(path: '/api/v1/tasks/{id}/skip', tags: ['Tasks'], summary: 'Passer tache', security: [['bearerAuth' => []]], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Tache ignoree.')]))])]
    public function tasksSkip(): void
    {
    }

    #[OA\Get(path: '/api/v1/tasks/history', tags: ['Tasks'], summary: 'Historique', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')), new OA\Property(property: 'links', type: 'object'), new OA\Property(property: 'meta', type: 'object')]))])]
    public function tasksHistory(): void
    {
    }

    #[OA\Get(path: '/api/v1/tasks/{id}', tags: ['Tasks'], summary: 'Detail tache', security: [['bearerAuth' => []]], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'task', type: 'object', example: ['id' => 12, 'type_tache' => 'classification_image', 'question' => 'Que montre cette image ?', 'options_reponse' => ['chat', 'chien', 'voiture'], 'nb_annotations_requises' => 3, 'statut' => 'nouvelle', 'is_sentinelle' => false, 'image' => ['id' => 42, 'url_stockage' => 'images/task-42.jpg', 'url' => 'http://localhost/storage/images/task-42.jpg', 'categorie' => 'animaux'], 'annotations_count' => 1, 'created_at' => '2026-04-22T09:30:00.000000Z']), new OA\Property(property: 'already_annotated', type: 'boolean', example: false)]))])]
    public function tasksShow(): void
    {
    }

    #[OA\Get(path: '/api/v1/wallet/balance', tags: ['Wallet'], summary: 'Solde', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'solde_virtuel', type: 'number', example: 1500)]))])]
    public function walletBalance(): void
    {
    }

    #[OA\Get(path: '/api/v1/wallet/transactions', tags: ['Wallet'], summary: 'Transactions', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 501, 'utilisateur_id' => 5, 'annotation_id' => 98, 'type' => 'gain', 'libelle' => 'Recompense annotation', 'montant' => 250, 'solde_avant' => 1250, 'solde_apres' => 1500, 'reference_tache' => 'task:12', 'created_at' => '2026-04-23T10:00:00.000000Z'])), new OA\Property(property: 'links', type: 'object', example: ['first' => 'http://localhost:8000/api/v1/wallet/transactions?page=1', 'last' => 'http://localhost:8000/api/v1/wallet/transactions?page=2', 'prev' => null, 'next' => 'http://localhost:8000/api/v1/wallet/transactions?page=2']), new OA\Property(property: 'meta', type: 'object', example: ['current_page' => 1, 'from' => 1, 'last_page' => 2, 'path' => 'http://localhost:8000/api/v1/wallet/transactions', 'per_page' => 20, 'to' => 20, 'total' => 30])]))])]
    public function walletTransactions(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/wallet/withdraw',
        tags: ['Wallet'],
        summary: 'Retrait',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['montant', 'methode_paiement'],
                properties: [
                    new OA\Property(property: 'montant', type: 'number', minimum: 100, example: 1500),
                    new OA\Property(property: 'methode_paiement', type: 'string', maxLength: 100, example: 'mobile_money_orange'),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Cree', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Demande de retrait enregistree.'), new OA\Property(property: 'solde_virtuel', type: 'number', example: 500), new OA\Property(property: 'transaction', type: 'object')]))]
    )]
    public function walletWithdraw(): void
    {
    }

    #[OA\Get(path: '/api/v1/admin/dashboard', tags: ['Admin'], summary: 'Dashboard', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Metriques dashboard admin recuperees.'), new OA\Property(property: 'data', type: 'object', example: ['annotations_aujourdhui' => 12, 'utilisateurs_inscrits' => 256, 'solde_total_distribue' => 125000])]))])]
    public function adminDashboard(): void
    {
    }

    #[OA\Get(path: '/api/v1/admin/users', tags: ['Admin'], summary: 'Liste users', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 5, 'name' => 'Awa Traore', 'telephone' => '+2250700000001', 'email' => 'awa@example.com', 'role' => 'contributeur', 'statut' => 'actif', 'score_confiance' => 87.5, 'solde_virtuel' => 1500, 'created_at' => '2026-04-20T08:15:00.000000Z'])), new OA\Property(property: 'links', type: 'object', example: ['first' => 'http://localhost:8000/api/v1/admin/users?page=1', 'last' => 'http://localhost:8000/api/v1/admin/users?page=3', 'prev' => null, 'next' => 'http://localhost:8000/api/v1/admin/users?page=2']), new OA\Property(property: 'meta', type: 'object', example: ['current_page' => 1, 'from' => 1, 'last_page' => 3, 'path' => 'http://localhost:8000/api/v1/admin/users', 'per_page' => 20, 'to' => 20, 'total' => 54])]))])]
    public function adminUsers(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/admin/users/{id}',
        tags: ['Admin'],
        summary: 'Modifier user',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['statut'],
                properties: [
                    new OA\Property(property: 'statut', type: 'string', enum: ['actif', 'suspendu'], example: 'suspendu'),
                    new OA\Property(property: 'motif', type: 'string', maxLength: 255, nullable: true, example: 'Suspicion de fraude'),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Statut utilisateur mis a jour.'), new OA\Property(property: 'user', type: 'object')]))]
    )]
    public function adminUpdateUser(): void
    {
    }

    #[OA\Get(path: '/api/v1/admin/alerts', tags: ['Admin'], summary: 'Alertes', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'alerts', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 18, 'severity' => 'high', 'reason' => 'Temps d\'execution suspectement faible.', 'temps_execution_ms' => 420, 'created_at' => '2026-04-23T09:45:00.000000Z', 'utilisateur' => ['id' => 5, 'name' => 'Awa Traore', 'telephone' => '+2250700000001'], 'tache' => ['id' => 12, 'question' => 'Que montre cette image ?']]))]))])]
    public function adminAlerts(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/tasks/upload',
        tags: ['Admin'],
        summary: 'Upload tasks',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['images', 'type_tache', 'question'],
                    properties: [
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary')
                        ),
                        new OA\Property(property: 'type_tache', type: 'string', maxLength: 100, example: 'classification_image'),
                        new OA\Property(property: 'question', type: 'string', example: 'Que montre cette image ?'),
                        new OA\Property(
                            property: 'options',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['chat', 'chien', 'voiture']
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Cree', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Upload traite et taches creees.'), new OA\Property(property: 'count', type: 'integer', example: 3), new OA\Property(property: 'tasks', type: 'array', items: new OA\Items(type: 'object'))]))]
    )]
    public function adminTasksUpload(): void
    {
    }

    #[OA\Get(path: '/api/v1/admin/datasets', tags: ['Admin'], summary: 'Datasets', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 4, 'nom' => 'Dataset initial', 'description' => 'Jeu de donnees de validation', 'version' => '1.0', 'nb_images' => 120, 'nb_annotations_validees' => 300, 'format_export' => 'json', 'created_at' => '2026-04-21T12:00:00.000000Z'])), new OA\Property(property: 'links', type: 'object', example: ['first' => 'http://localhost:8000/api/v1/admin/datasets?page=1', 'last' => 'http://localhost:8000/api/v1/admin/datasets?page=2', 'prev' => null, 'next' => 'http://localhost:8000/api/v1/admin/datasets?page=2']), new OA\Property(property: 'meta', type: 'object', example: ['current_page' => 1, 'from' => 1, 'last_page' => 2, 'path' => 'http://localhost:8000/api/v1/admin/datasets', 'per_page' => 20, 'to' => 20, 'total' => 24])]))])]
    public function adminDatasets(): void
    {
    }

    #[OA\Get(path: '/api/v1/admin/datasets/{id}/export', tags: ['Admin'], summary: 'Export dataset', security: [['bearerAuth' => []]], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'dataset', type: 'object', example: ['id' => 4, 'nom' => 'Dataset initial', 'description' => 'Jeu de donnees de validation', 'version' => '1.0', 'nb_images' => 120, 'nb_annotations_validees' => 300, 'format_export' => 'json', 'created_at' => '2026-04-21T12:00:00.000000Z']), new OA\Property(property: 'annotations', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 98, 'tache_id' => 12, 'utilisateur_id' => 5, 'reponse_choisie' => 'chat', 'temps_execution_ms' => 1850, 'created_at' => '2026-04-23T10:00:00.000000Z']))]))])]
    public function adminDatasetExport(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/admin/config',
        tags: ['Admin'],
        summary: 'Config systeme',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'seuil_consensus', type: 'number', minimum: 0, maximum: 100, example: 66),
                    new OA\Property(property: 'freq_sentinelle', type: 'integer', minimum: 0, example: 10),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Configuration systeme mise a jour.'), new OA\Property(property: 'config', type: 'object', example: ['seuil_consensus' => 66, 'freq_sentinelle' => 10])]))]
    )]
    public function adminConfig(): void
    {
    }

    #[OA\Post(path: '/api/v1/sync/push', tags: ['Sync'], summary: 'Sync push', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')), responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'message', type: 'string', example: 'Synchronisation push terminee.'), new OA\Property(property: 'created', type: 'integer', example: 12), new OA\Property(property: 'ignored', type: 'integer', example: 2)]))])]
    public function syncPush(): void
    {
    }

    #[OA\Get(path: '/api/v1/sync/pull', tags: ['Sync'], summary: 'Sync pull', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'server_timestamp', type: 'string', format: 'date-time', example: '2026-04-23T10:00:00Z'), new OA\Property(property: 'tasks', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 12, 'type_tache' => 'classification_image', 'question' => 'Que montre cette image ?', 'options_reponse' => ['chat', 'chien', 'voiture'], 'nb_annotations_requises' => 3, 'statut' => 'en_cours', 'is_sentinelle' => false, 'image' => ['id' => 42, 'url_stockage' => 'images/task-42.jpg', 'url' => 'http://localhost/storage/images/task-42.jpg', 'categorie' => 'animaux'], 'annotations_count' => 2, 'created_at' => '2026-04-22T09:30:00.000000Z'])), new OA\Property(property: 'transactions', type: 'array', items: new OA\Items(type: 'object', example: ['id' => 501, 'utilisateur_id' => 5, 'annotation_id' => 98, 'type' => 'gain', 'libelle' => 'Recompense annotation', 'montant' => 250, 'solde_avant' => 1250, 'solde_apres' => 1500, 'reference_tache' => 'task:12', 'created_at' => '2026-04-23T10:00:00.000000Z']))]))])]
    public function syncPull(): void
    {
    }
}
