# Sistema de Indicação com Árvore Binária

## 📋 Descrição

Sistema de indicação que implementa uma estrutura de árvore binária para gerenciar usuários e seus pontos acumulados. O sistema permite adicionar usuários à rede de indicação seguindo a regra de alocação da esquerda para direita, e calcula automaticamente os pontos totais de cada lado da árvore.

## 🎯 Caso de Uso

- **Usuário 1**: Primeiro usuário se torna a raiz da árvore
- **Usuário 2**: Indicado pelo usuário 1, alocado à esquerda (200 pontos)
- **Usuário 3**: Indicado pelo usuário 1, alocado à direita (100 pontos)
- **Placar**: Usuário 1 tem 200 pontos à esquerda e 100 pontos à direita

## 🏗️ Arquitetura

### Padrão Composite

O sistema implementa o **Padrão Composite** para representar a estrutura hierárquica da árvore binária:

```php
interface UserComponent {
    public function calculatePoints(): int;
}

class User implements UserComponent {
    private ?UserComponent $leftChild = null;
    private ?UserComponent $rightChild = null;
    
    public function calculatePoints(): int {
        $totalPoints = $this->points;
        if ($this->leftChild !== null) {
            $totalPoints += $this->leftChild->calculatePoints();
        }
        if ($this->rightChild !== null) {
            $totalPoints += $this->rightChild->calculatePoints();
        }
        return $totalPoints;
    }
}
```

**Benefícios do Composite:**
- Tratamento uniforme de nós individuais e subárvores
- Cálculo recursivo de pontos de forma transparente
- Facilita extensões futuras (ex: diferentes tipos de nós)
- Segue o princípio de responsabilidade única

### Estrutura do Projeto

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

## 🗄️ Banco de Dados

### Tabelas

- **users**: Dados dos usuários (id, name, current_points)
- **binary_tree_structure**: Estrutura da árvore (user_id, parent_id, position, level)
- **referrals**: Relacionamento de indicações (referrer_id, referred_id)
- **points_history**: Histórico de pontos (user_id, points, operation, description)

### Schema

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    current_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE binary_tree_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    parent_id INT NULL,
    position ENUM('root', 'left', 'right') NOT NULL,
    level INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES users(id)
);
```

## 🚀 Tecnologias

- **PHP 8.3+** com PSR-4 autoload
- **Composer** para gerenciamento de dependências
- **MySQL 8.0** como banco de dados
- **Twig** para templates
- **Tailwind CSS** para estilização
- **jQuery** para interações frontend
- **Docker** para containerização

## 🛠️ Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd referral-sys
```

2. **Configure as variáveis de ambiente**
```bash
cp .env.example .env
# Edite o .env com suas configurações
```

3. **Suba os containers**
```bash
docker compose up -d --build
```

4. **Acesse a aplicação**
```
http://localhost:8080
```

## 📱 Funcionalidades

### Interface Web
- **Visualização da árvore**: Representação gráfica da estrutura binária
- **Formulário de cadastro**: Adicionar novos usuários à rede
- **Placar em tempo real**: Pontos acumulados por lado da árvore
- **Lista de usuários**: Todos os usuários cadastrados

### Regras de Negócio
- **Alocação automática**: Usuários são alocados da esquerda para direita
- **Cálculo recursivo**: Pontos são somados recursivamente na árvore
- **Validação de posições**: Impede alocação em posições já ocupadas
- **Transações**: Operações atômicas para consistência dos dados

## 🧪 Testes

O sistema inclui dados de exemplo (seed) que demonstram o caso de uso:
- Usuário 1 como raiz
- Usuário 2 à esquerda com 200 pontos
- Usuário 3 à direita com 100 pontos

## 📚 Referências

- [Padrão Composite - Refactoring Guru](https://refactoring.guru/design-patterns/composite)
- [PSR-4 Autoloader](https://www.php-fig.org/psr/psr-4/)
- [Twig Template Engine](https://twig.symfony.com/)
- [Tailwind CSS](https://tailwindcss.com/)

## 👨‍💻 Desenvolvedor

Sistema desenvolvido como teste técnico, implementando boas práticas de desenvolvimento, padrões de design e arquitetura limpa.