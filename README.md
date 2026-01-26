# Multi-Agent System for Intelligent Customer Experience Incident Management

Multi-agent system that automates and structures the customer incident management flow, from receiving the message to automatically creating tickets in Linear when necessary.

## 🎯 Description

This project implements a multi-agent system that processes Customer Experience (CX) incidents through six specialized agents that work in coordination:

- **Interpreter**: Analyzes customer text and extracts key information
- **Classifier**: Classifies the incident by type and affected area
- **Validator**: Evaluates if there is sufficient information to process
- **Prioritizer**: Calculates priority based on impact, urgency, and severity
- **Decision Maker**: Decides whether to escalate to development or manage from CX
- **Linear Writer**: Automatically creates tickets in Linear when necessary

## 🚀 Features

- ✅ Automatic incident processing with multi-agent system
- ✅ Complete web interface for ticket management
- ✅ Linear integration for automatic issue creation
- ✅ Support for multiple AI providers (Anthropic, OpenAI, Gemini, Mistral, Ollama)
- ✅ Flexible configuration: use AI or predefined rules per agent
- ✅ Real-time processing with visual feedback
- ✅ Batch processing of multiple tickets
- ✅ Complete traceability of all decisions made

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- Database (MySQL, PostgreSQL, SQLite)
- (Optional) API keys for AI services
- (Optional) Linear API key

## 🔧 Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd agents
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure the environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure the database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations:
```bash
php artisan migrate
```

6. (Optional) Configure AI services in `.env`:
```env
NEURON_AI_USE_LLM=true
NEURON_AI_DEFAULT_PROVIDER=anthropic
NEURON_AI_PROVIDERS_ANTHROPIC_KEY=your_api_key
NEURON_AI_PROVIDERS_ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
```

7. (Optional) Configure Linear in `.env`:
```env
LINEAR_API_KEY=your_api_key
LINEAR_TEAM_ID=your_team_id
```

8. Build assets:
```bash
npm run build
```

9. Start the server:
```bash
php artisan serve
```

## 🎮 Usage

### Accessing the application

Once the server is started, access the URL shown by Laravel (default `http://localhost:8000`) in your browser.

### Ticket management

- **View tickets**: Navigate to `/support` to see the ticket list
- **Process ticket**: Click on a ticket and select "Process" to run the multi-agent workflow
- **Real-time processing**: Use the "Process with streaming" button to see progress in real-time
- **Batch processing**: Select multiple tickets and process them all at once

### Configuration

Access `/settings` to configure:

- Enable/disable individual agents
- Choose between AI or predefined rules for each agent
- Configure AI providers
- Configure Linear integration

## 🏗️ Architecture

The system uses a multi-agent architecture where:

- Each agent has a specific responsibility
- Agents work sequentially, passing information between them
- A central orchestrator coordinates the complete flow
- The entire process is recorded for traceability

## 🛠️ Technologies

- **Laravel 12**: PHP web framework
- **Neuron AI**: Language model integration
- **Linear API**: Automatic ticket creation
- **Tailwind CSS**: Interface styling
- **Pest**: Testing framework

## 📁 Project Structure

```
app/
├── AI/
│   ├── Agents/          # Agent implementations
│   ├── Neuron/          # AI-powered agents
│   ├── Orchestrator/     # Orchestrator and shared state
│   └── Prompts/          # Prompts for AI agents
├── Http/
│   └── Controllers/      # Application controllers
├── Integrations/
│   └── Linear/          # Linear integration
└── Models/              # Data models

resources/
└── views/               # Blade views

routes/
└── web.php              # Application routes
```

## 🧪 Testing

Run tests:
```bash
php artisan test
```

## 📚 Documentation

For more details about the project, see [PROJECTE.md](PROJECTE.md).

## 📝 License

This project is licensed under the MIT license.

## 👤 Author

**Eduard Altimiras Duocastella**

---

*Project developed as a Capstone final project.*
