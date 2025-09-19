# Desafio

O desafio consistia em fazer um sistema de indicação que tivesse uma rede para somar os pontos, essa rede precisaria ser na estrutura de arvore binária

No caso de uso, deve ser possível cadastrar 3 usuários no sistema, o primeiro usuário que se cadastrar será o topo da árvore (root), o segundo usuário deverá ser alocado ao lado esquerdo do root (left), e o terceiro usuário ao lado direito (right). Cada nó das extremidades da árvore deverá somar na pontuação do nó raiz. 

# Solução

O projeto foi estruturado em uma **Arquitetura em Camadas**, separando claramente as responsabilidades de apresentação (Twig), lógica de negócio (Services) e acesso a dados (Repositories). 

Para a modelagem da rede de referências hierárquica, o padrão de projeto [**Composite**](https://refactoring.guru/pt-br/design-patterns/composite) foi a escolha natural. Ele permitiu compor os usuários em uma estrutura de árvore, onde a principal vantagem é a capacidade de processar tanto um nó individual quanto uma sub-rede inteira de forma uniforme. Essa funcionalidade foi implementada através de uma função **recursiva** que percorre a árvore para agregar os pontos totais de cada ramo.

Dada a especificidade do projeto, optei por uma implementação "pura" (vanilla), sem a utilização de um framework full-stack. Isso evitou o overhead de componentes desnecessários (como ORM, migrations, etc.), resultando em uma aplicação mais leve e direta ao ponto.

Para demonstrar práticas modernas de desenvolvimento, a aplicação foi totalmente containerizada com **Docker**, utilizando um banco de dados **MySQL** para a persistência dos dados.

## Esquema do banco de dados

Plataforma: [DrawDB](https://www.drawdb.app)

<img src="https://i.ibb.co/MxvD8CpM/Captura-de-tela-2025-09-18-224147.png">

## Estrutura do projeto

```
src/
├── Model/
│   ├── UserComponent.php    # Interface do Composite
│   └── User.php            # Implementação do Composite
├── Repository/
│   └── UserRepository.php  # Acesso aos dados
├── Service/
│   └── UserService.php     # Lógica de negócio
└── Database/
    ├── Connection.php      # Singleton para conexão
    ├── BaseRepository.php  # Repository base
    └── DatabaseManager.php # Gerenciamento do banco
```

## Requisitos funcionais

- **Visualização da árvore**: Representação gráfica da estrutura binária
- **Formulário de cadastro**: Adicionar novos usuários à rede
- **Placar em tempo real**: Pontos acumulados por lado da árvore
- **Lista de usuários**: Todos os usuários cadastrados

## Requisitos não funcionais

- **PHP 8.3+** com PSR-4 autoload
- **Composer** para gerenciamento de dependências
- **MySQL 8.0** como banco de dados
- **Twig** para templates
- **Tailwind CSS** para estilização
- **jQuery** para interações frontend
- **Docker** para containerização

## Requisitos de domínio

- **Alocação automática**: Usuários são alocados da esquerda para direita
- **Cálculo recursivo**: Pontos são somados recursivamente na árvore
- **Transações**: Operações atômicas para consistência dos dados

## Vídeo de explicação:
https://youtu.be/PnjiSIS5CKI
## Como rodar o projeto

1. **Clone o repositório**

```bash
git clone <repository-url>
cd referral-sys
```

2. **Configure as variáveis de ambiente**

```bash
cp .env.example .env
```

3. **Instale as dependências**

```bash
composer install
```

4. **Suba os containers**

```bash
docker compose up -d --build
```

5. **Acesse a aplicação**

```
http://localhost:8080
```

## Considerações finais
Este projeto me proporcionou a oportunidade de revisitar matérias de Estruturas de Dados e Design Patterns que estudei na faculdade, o que foi muito gratificante. Me senti à vontade com a maioria dos conceitos, mas resolvi ir além. Implementei uma arquitetura em camadas e adicionei algumas funcionalidades que considero comuns no Laravel, como o `dd` (dump and die) e transactions no banco de dados para manter a atomicidade.

Além disso, utilizei inteligência artificial no frontend para gerar a representação gráfica da árvore, assim como em algumas consultas técnicas que me ajudaram a implementar funcionalidades típicas do Laravel. A IA também foi fundamental na criação da documentação do projeto.

Estou extremamente satisfeito com o resultado final e espero que esse projeto seja a minha porta de entrada na GoDev.
## **Referências**

- [Padrão Composite - Refactoring Guru](https://refactoring.guru/design-patterns/composite)
- [Twig Template Engine](https://twig.symfony.com/)
- [Tailwind CSS](https://tailwindcss.com/)