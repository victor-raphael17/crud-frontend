# $_SERVER

`$_SERVER` é um **array associativo** (chave => valor), também chamado de *superglobal* — disponível em qualquer escopo sem precisar de `global`.

Ela contém informações de **ambos** (servidor e cliente):

## Do servidor PHP (ambiente)

- `$_SERVER['SERVER_NAME']` — nome do host (ex: `localhost`)
- `$_SERVER['SERVER_PORT']` — porta (ex: `8000`)
- `$_SERVER['DOCUMENT_ROOT']` — diretório raiz do servidor

## Da requisição recebida (cliente)

- `$_SERVER['REQUEST_METHOD']` — `GET`, `POST`, etc.
- `$_SERVER['REQUEST_URI']` — caminho acessado (ex: `/api/users`)
- `$_SERVER['HTTP_ORIGIN']` — origem de quem fez a requisição
- `$_SERVER['HTTP_USER_AGENT']` — navegador/cliente usado

As chaves que começam com `HTTP_` vêm dos **headers da requisição**. As demais geralmente vêm do **servidor/ambiente**.

---

# Métodos HTTP — Frontend (JS) + Backend (PHP)

O fluxo é sempre o mesmo:

1. O **frontend** faz uma requisição com `fetch()` informando o método, headers e body (quando necessário)
2. O **backend** lê `$_SERVER['REQUEST_METHOD']` para saber qual método foi usado e responde de acordo

Abaixo, cada método com a sintaxe completa dos dois lados.

---

## GET — Buscar dados

Usado para **ler/listar** recursos. Não envia body.

### Frontend (JS)

```js
const response = await fetch('http://localhost:8000/api/users');
```

#### Por que não tem segundo argumento?

O `fetch()` aceita dois argumentos: `fetch(url, opções)`. Quando você não passa o segundo argumento,
ele assume `method: 'GET'` automaticamente. Então esses dois são idênticos:

```js
fetch('http://localhost:8000/api/users')                    // implícito
fetch('http://localhost:8000/api/users', { method: 'GET' }) // explícito — desnecessário
```

#### E o body?

GET **não tem body**. Você está **pedindo** dados, não enviando. Se precisar filtrar, passa direto na URL
como **query string** — aqueles `?chave=valor` no final:

```js
// Buscar todos os usuários
fetch('http://localhost:8000/api/users')

// Buscar um usuário específico — o filtro vai na URL
fetch('http://localhost:8000/api/users?id=1')

// Múltiplos filtros — separados por &
fetch('http://localhost:8000/api/users?age=25&name=Victor')
```

#### `response.json()` — por que precisa disso?

O `fetch` retorna um objeto `Response`. O body da resposta vem como stream (dados brutos).
`response.json()` lê esse stream e converte pra um objeto JavaScript:

```js
const response = await fetch('http://localhost:8000/api/users');
// response é um objeto Response com status, headers, etc.
// O body ainda não foi lido — é um stream esperando ser consumido

const data = await response.json();
// Agora sim: leu o stream, converteu de string JSON → objeto JS
// data = { users: [{name: "Victor", age: 24, ...}] }
```

É `await` porque ler o stream é uma operação assíncrona — os dados podem não ter chegado inteiros ainda.

### Backend (PHP)

```php
case 'GET':
    $json = file_get_contents($dataFile);
    echo $json;
    break;
```

#### Quebrando linha por linha:

**`file_get_contents($dataFile)`** — lê o arquivo `data.json` inteiro e retorna como string.
O `$dataFile` foi definido lá em cima como `__DIR__ . '/../data/data.json'`.

```php
$json = file_get_contents($dataFile);
// $json = '{"users": [{"name": "Victor", "age": 24}]}'  ← é uma STRING
```

**`echo $json`** — "cospe" essa string pro cliente. O `echo` no PHP é o que envia dados na resposta HTTP.
Tudo que você dá `echo` vira o **body da resposta** que o frontend recebe.

```
O que o echo faz:

echo $json;
   ↓
Resposta HTTP enviada:
   HTTP/1.1 200 OK
   Content-Type: application/json

   {"users": [{"name": "Victor", "age": 24}]}  ← isso é o que o response.json() lê no frontend
```

#### Fluxo completo GET:

```
Frontend:  fetch('http://localhost:8000/api/users')
                          ↓
Backend:   file_get_contents('data.json')  → '{"users": [...]}'
                          ↓
Backend:   echo '{"users": [...]}'  → envia como resposta HTTP
                          ↓
Frontend:  response.json()  → converte string JSON → objeto JS → { users: [...] }
```

---

## POST — Criar dados

Usado para **criar** um novo recurso. Envia dados no body.

### Frontend (JS)

```js
const response = await fetch('http://localhost:8000/api/users', {
    method: 'POST',                                    // especifica o método
    headers: { 'Content-Type': 'application/json' },   // diz que o body é JSON
    body: JSON.stringify({                              // converte o objeto JS para string JSON
        name: 'João',
        age: 25,
        email: 'joao@email.com'
    }),
});

const created = await response.json(); // resposta do servidor
```

### Backend (PHP)

```php
case 'POST':
    $input = json_decode(file_get_contents('php://input'), true);
```

Vamos quebrar essa linha em pedaços:

#### 1. `php://input` — o que é isso?

O PHP tem "wrappers" — endereços especiais que não são arquivos de verdade, mas o PHP trata como se fossem.
O formato é sempre `protocolo://recurso`:

- `php://input` → o body cru (raw) que o cliente enviou na requisição
- `php://output` → a saída (equivalente a `echo`)
- `file://` → arquivos normais (é o padrão, por isso você não precisa escrever)

Então `php://input` é um **endereço virtual** que aponta pro body da requisição.
Quando o frontend envia `{"name": "João", "age": 25}`, esse conteúdo fica disponível em `php://input`.

#### 2. `file_get_contents()` — por que precisa disso?

`php://input` é um **stream** — pense nele como um cano de água. Os dados estão lá, mas você precisa
"abrir a torneira" pra pegar. `file_get_contents()` faz isso: lê todo o conteúdo do stream e devolve como string.

```php
file_get_contents('php://input')
// Retorna: '{"name": "João", "age": 25}'  ← isso é uma STRING, não um array
```

É a mesma função que lê arquivos normais (`file_get_contents('data.json')`), mas aqui ela lê o body da requisição HTTP.

#### 3. `json_decode(..., true)` — por que o `true`?

O `json_decode` converte uma string JSON em algo que o PHP entende. O segundo parâmetro muda **o tipo** do resultado:

```php
// Sem true → retorna um OBJETO (stdClass)
$input = json_decode('{"name": "João"}');
$input->name;  // acessa com seta →

// Com true → retorna um ARRAY ASSOCIATIVO
$input = json_decode('{"name": "João"}', true);
$input['name'];  // acessa com colchetes []
```

Usamos `true` porque arrays associativos são mais práticos no PHP — mais fáceis de manipular,
combinar com `array_merge`, salvar com `json_encode`, etc.

#### Juntando tudo — o fluxo completo:

```
Frontend envia: fetch(url, { body: JSON.stringify({name: "João", age: 25}) })
                                          ↓
php://input contém:                '{"name": "João", "age": 25}'  ← string JSON crua
                                          ↓
file_get_contents('php://input'):  '{"name": "João", "age": 25}'  ← mesma string, agora em variável PHP
                                          ↓
json_decode(..., true):            ['name' => 'João', 'age' => 25]  ← array PHP utilizável
```

---

```php
    // Validação
    if (!isset($input['name']) || !isset($input['age'])) {
        http_response_code(400); // 400 = Bad Request
        echo json_encode(['error' => 'Name and age are required']);
        exit;
    }

    // Lê dados existentes
    $data = json_decode(file_get_contents($dataFile), true);

    // Cria o novo usuário
    $newUser = [
        'name' => $input['name'],
        'age' => (int) $input['age'],
        'email' => $input['email'],
    ];

    // Adiciona ao array
    $data['users'][] = $newUser;

    // Salva no arquivo
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

    http_response_code(201); // 201 = Created
    echo json_encode($newUser);
    break;
```

**Notas:**
- `headers: { 'Content-Type': 'application/json' }` é **obrigatório** quando envia JSON — sem esse header, o servidor não sabe que o body é JSON (poderia ser form-data, XML, texto puro, etc.)
- `php://input` funciona para qualquer método que envie body (POST, PUT, PATCH, DELETE)

---

## PUT — Atualizar dados (substituição completa)

Usado para **substituir** um recurso inteiro. Você envia **todos** os campos, mesmo os que não mudaram.

### Frontend (JS)

```js
const response = await fetch('http://localhost:8000/api/users?index=0', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        name: 'Victor Atualizado',
        age: 25,
        email: 'novo@email.com'
    }),
});

const updated = await response.json();
```

#### Por que `?index=0` na URL se já tem body?

Porque URL e body têm **propósitos diferentes**:

- **URL (`?index=0`)** → diz **qual** recurso você quer alterar (identificação)
- **body (`{name, age, email}`)** → diz **o que** colocar no lugar (dados novos)

```
PUT /api/users?index=0       ← "quero alterar o usuário na posição 0"
body: {name: "Victor", ...}  ← "substitua por esses dados"
```

É como dizer: "vai na gaveta 0 e troca tudo que tem lá por isso aqui".

#### Por que envia TODOS os campos?

PUT faz **substituição completa**. Se você não enviar o `email`, ele some:

```js
// Usuário atual: { name: "Victor", age: 24, email: "victor@email.com" }

// PUT sem email:
body: JSON.stringify({ name: "Victor", age: 25 })
// Resultado: { name: "Victor", age: 25 }  ← email SUMIU

// PUT com tudo:
body: JSON.stringify({ name: "Victor", age: 25, email: "victor@email.com" })
// Resultado: { name: "Victor", age: 25, email: "victor@email.com" }  ← tudo certo
```

### Backend (PHP)

```php
case 'PUT':
    $input = json_decode(file_get_contents('php://input'), true);
    $index = $_GET['index'] ?? null;
```

#### `$_GET['index']` — mas a requisição é PUT, não GET?

O nome `$_GET` confunde, mas ele **não tem relação com o método HTTP GET**. O `$_GET` pega qualquer
parâmetro que esteja na **query string da URL** (o que vem depois do `?`), independente do método:

```
PUT /api/users?index=0
                ↑
                $_GET['index'] = "0"  ← funciona em PUT, DELETE, PATCH, qualquer método
```

O nome `$_GET` é uma herança histórica ruim do PHP. Deveria se chamar `$_QUERY` ou algo assim,
mas ficou `$_GET` e todo mundo tem que conviver com isso.

#### `?? null` — por que não só `$_GET['index']`?

Se a URL não tiver `?index=...`, o `$_GET['index']` não existe — e o PHP dá um warning.
O `?? null` diz: "se não existir, usa `null` em vez de dar erro".

```php
// URL: /api/users?index=0
$index = $_GET['index'] ?? null;  // $index = "0"

// URL: /api/users  (sem ?index)
$index = $_GET['index'] ?? null;  // $index = null  ← sem warning
$index = $_GET['index'];          // ⚠ PHP Warning: Undefined array key "index"
```

---

```php
    if ($index === null || !isset($input['name']) || !isset($input['age'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Index, name and age are required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
```

#### Por que duas validações separadas?

São validações **diferentes**:

1. **400 (Bad Request)** — o cliente mandou dados errados/incompletos. Culpa do **cliente**.
2. **404 (Not Found)** — os dados estão corretos, mas o usuário não existe. Não é culpa de ninguém.

```
PUT /api/users              → 400 (cadê o index?)
PUT /api/users?index=0      → 400 (cadê o name e age no body?)
PUT /api/users?index=999    → 404 (index 999 não existe)
PUT /api/users?index=0 + body completo → 200 ✓
```

---

```php
    // Substitui o usuário inteiro
    $data['users'][$index] = [
        'name' => $input['name'],
        'age' => (int) $input['age'],
        'email' => $input['email'],
    ];

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode($data['users'][$index]);
    break;
```

#### `(int) $input['age']` — por que o cast?

O JSON que chega pode ter `"age": "25"` (string) em vez de `"age": 25` (número).
O `(int)` garante que **sempre** salva como número inteiro, independente do que o cliente mandou.

```php
(int) "25"   // → 25 (número)
(int) 25     // → 25 (já era número, não muda nada)
(int) "abc"  // → 0  (não conseguiu converter)
```

#### `JSON_PRETTY_PRINT` — por que?

Sem isso, o `json_encode` salva tudo em uma linha só:

```json
{"users":[{"name":"Victor","age":24}]}
```

Com `JSON_PRETTY_PRINT`, fica formatado e legível:

```json
{
    "users": [
        {
            "name": "Victor",
            "age": 24
        }
    ]
}
```

Não muda nada pro código — é só pra humanos conseguirem ler o arquivo `data.json`.

---

## PATCH — Atualizar dados (parcial)

Usado para **atualizar apenas alguns campos**, sem precisar enviar tudo.

### Frontend (JS)

```js
const response = await fetch('http://localhost:8000/api/users?index=0', {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        age: 30
    }),
});

const updated = await response.json();
```

#### Qual a diferença entre PUT e PATCH?

Imagine que o usuário atual é `{ name: "Victor", age: 24, email: "victor@email.com" }`:

```js
// PUT — substitui TUDO. Precisa mandar todos os campos.
// Se não mandar email, ele some.
PUT  body: { name: "Victor", age: 30, email: "victor@email.com" }
// Resultado: { name: "Victor", age: 30, email: "victor@email.com" }

// PATCH — atualiza SÓ o que você mandou. O resto fica como está.
PATCH body: { age: 30 }
// Resultado: { name: "Victor", age: 30, email: "victor@email.com" }
//                                  ↑ só isso mudou
```

PATCH é como dizer "muda só a idade, o resto nem encosta".

### Backend (PHP)

```php
case 'PATCH':
    $input = json_decode(file_get_contents('php://input'), true);
    $index = $_GET['index'] ?? null;

    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $data['users'][$index] = array_merge($data['users'][$index], $input);
```

#### `array_merge()` — como funciona a mágica?

É ela que faz o PATCH funcionar. O `array_merge` junta dois arrays — se tiver chaves repetidas,
o **segundo array vence** (sobrescreve o primeiro):

```php
$atual = ['name' => 'Victor', 'age' => 24, 'email' => 'victor@email.com'];
$input = ['age' => 30];

array_merge($atual, $input);
// Resultado: ['name' => 'Victor', 'age' => 30, 'email' => 'victor@email.com']
//                                        ↑ o 30 do $input substituiu o 24 do $atual
//                                        o resto ficou intacto
```

A **ordem dos argumentos importa**: `array_merge($atual, $input)` — o segundo sobrescreve o primeiro.
Se fosse ao contrário (`array_merge($input, $atual)`), os dados novos seriam sobrescritos pelos antigos
e nada mudaria.

#### Por que no PUT não usa `array_merge`?

Porque PUT **substitui tudo**. Ele cria um array novo do zero:

```php
// PUT — ignora o que existia, cria do zero
$data['users'][$index] = [
    'name' => $input['name'],
    'age' => (int) $input['age'],
    'email' => $input['email'],
];

// PATCH — pega o que existia e mescla com o que chegou
$data['users'][$index] = array_merge($data['users'][$index], $input);
```

---

```php
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode($data['users'][$index]);
    break;
```

O padrão é sempre o mesmo: salva no arquivo → devolve o resultado pro frontend.

---

## DELETE — Remover dados

Usado para **excluir** um recurso. Geralmente não envia body.

### Frontend (JS)

```js
const response = await fetch('http://localhost:8000/api/users?index=0', {
    method: 'DELETE',
});

const result = await response.json();
```

#### Por que não tem body?

Pelo mesmo motivo do GET — você não está **enviando** dados, está dizendo "deleta esse aqui".
O `?index=0` na URL já diz **qual** recurso apagar. Não precisa de mais nada.

```js
// DELETE não precisa de headers nem body — só method e URL
fetch('http://localhost:8000/api/users?index=0', {
    method: 'DELETE',
});
// Compara com POST que precisa de tudo:
fetch('http://localhost:8000/api/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: 'João', age: 25 }),
});
```

### Backend (PHP)

```php
case 'DELETE':
    $index = $_GET['index'] ?? null;
```

#### Por que não tem `php://input` aqui?

Porque DELETE não manda body (normalmente). Os dados que o backend precisa (qual usuário deletar)
vêm da **URL** via `$_GET`, não do body. Então não precisa de `php://input` nem `json_decode`.

---

```php
    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $removed = $data['users'][$index];
    array_splice($data['users'], $index, 1);
```

#### `array_splice()` — por que não usar `unset()`?

Ambos removem elementos, mas `unset()` deixa **buracos** nos índices:

```php
$users = ['Ana', 'Bruno', 'Carlos'];

// Com unset:
unset($users[1]); // remove Bruno
// $users = [0 => 'Ana', 2 => 'Carlos']  ← índice 1 sumiu, tem um buraco

// Com array_splice:
array_splice($users, 1, 1); // remove 1 elemento a partir da posição 1
// $users = [0 => 'Ana', 1 => 'Carlos']  ← reindexou, sem buraco
```

O `array_splice($array, $posição, $quantidade)` tem 3 argumentos:
- `$array` → o array original
- `$posição` → a partir de onde remover (índice 1 = segundo elemento)
- `$quantidade` → quantos elementos remover (1 = só um)

Se usasse `unset`, quando salvar no JSON os índices quebram e viram chaves de objeto em vez de array:

```json
// Com unset (bugado):
{"users": {"0": "Ana", "2": "Carlos"}}  ← virou objeto, não é mais array

// Com array_splice (correto):
{"users": ["Ana", "Carlos"]}  ← continua sendo array
```

---

```php
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode(['deleted' => $removed]);
    break;
```

#### Por que retorna o usuário deletado?

É uma boa prática — o frontend pode usar pra mostrar "Usuário X foi removido" ou pra dar a opção de desfazer.
O `$removed` foi salvo **antes** do `array_splice` justamente pra isso — depois de remover, ele não existe mais.

---

## OPTIONS — Preflight (CORS)

### O que é e por que existe?

Quando seu frontend (`localhost:5500`) faz uma requisição pro backend (`localhost:8000`), são **origens diferentes**
(porta diferente = origem diferente). O navegador bloqueia isso por segurança — é a política de **CORS**
(Cross-Origin Resource Sharing).

Antes de mandar a requisição real (POST, PUT, etc.), o navegador manda uma requisição **OPTIONS** primeiro,
perguntando: "ei servidor, o `localhost:5500` pode te fazer requisições POST com JSON?".

```
1. Navegador → servidor:  OPTIONS /api/users  "posso fazer POST com JSON?"
2. Servidor → navegador:  204 + headers CORS  "sim, pode"
3. Navegador → servidor:  POST /api/users     (a requisição real)
4. Servidor → navegador:  201 Created         (a resposta real)
```

Se o servidor não responder o OPTIONS corretamente, o passo 3 **nunca acontece** — o navegador bloqueia.

### Frontend (JS)

```js
// Você NÃO escreve código para OPTIONS.
// O navegador faz isso sozinho, automaticamente, nos bastidores.
// Você nunca vai ver um fetch com method: 'OPTIONS' no seu código.
```

#### Quando o navegador envia o preflight?

Quando detecta que a requisição é "não-simples":

- Método diferente de GET ou POST simples (PUT, PATCH, DELETE)
- OU header customizado como `Content-Type: application/json`

Ou seja, até o seu POST dispara preflight, porque tem o header `Content-Type: application/json`.

### Backend (PHP)

```php
// No router.php — responde ao preflight antes de rotear
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
```

#### Por que fica no `router.php` e não no `api.php`?

Porque o preflight precisa ser respondido **antes de qualquer processamento**. Se o OPTIONS chegasse
no `api.php`, iria entrar no `switch` e cair no `default` com erro 405. O router intercepta antes:

```
Requisição chega
    ↓
router.php: é OPTIONS? → sim → 204 + exit (acabou aqui)
                        → não → continua pro api.php
```

#### `http_response_code(204)` — por que 204?

204 = "No Content". O servidor está dizendo "recebi sua pergunta, está tudo ok, mas não tenho nada pra
te devolver no body". Faz sentido porque o preflight é só uma verificação — não tem dados pra retornar.

#### E os headers de CORS?

Eles são definidos **acima** do `if (OPTIONS)`, no começo do `router.php`:

```php
$allowedOrigins = ['http://localhost:5500', 'http://127.0.0.1:5500'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

in_array($origin, $allowedOrigins) ?
    header("Access-Control-Allow-Origin: $origin") : null;
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

Quebrando:

- **`Access-Control-Allow-Origin: $origin`** → "aceito requisições vindas dessa origem"
  - Só adiciona o header se a origem estiver na lista `$allowedOrigins`
  - Se vier de `localhost:3000` (não está na lista), o header não é enviado e o navegador bloqueia

- **`Access-Control-Allow-Methods`** → "aceito esses métodos HTTP"

- **`Access-Control-Allow-Headers: Content-Type`** → "aceito esse header na requisição"
  - Sem isso, o navegador bloqueia qualquer requisição que mande `Content-Type: application/json`

---

## Resumo visual do fluxo

```
Frontend (fetch)                     Backend (PHP)
─────────────────                    ─────────────
fetch(url)                    →      GET    → file_get_contents() → echo
fetch(url, {method:'POST'})   →      POST   → php://input → json_decode → salvar
fetch(url, {method:'PUT'})    →      PUT    → php://input → substituir tudo
fetch(url, {method:'PATCH'})  →      PATCH  → php://input → array_merge (parcial)
fetch(url, {method:'DELETE'}) →      DELETE → $_GET['index'] → array_splice
[automático pelo navegador]   →      OPTIONS → 204 (preflight CORS)
```

## Códigos de status HTTP comuns

| Código | Significado | Quando usar |
|--------|-------------|-------------|
| `200` | OK | GET, PUT, PATCH bem-sucedidos |
| `201` | Created | POST criou um recurso |
| `204` | No Content | OPTIONS ou DELETE sem body de retorno |
| `400` | Bad Request | Dados inválidos ou faltando |
| `404` | Not Found | Recurso não existe |
| `405` | Method Not Allowed | Método HTTP não suportado |
