<?php
/**
 * ملف التكامل مع Manous API
 * Manous API Integration File
 * 
 * @package AlAbasiAccounting
 * @version 1.0.0
 * @author alabasi2025
 */

// منع الوصول المباشر
if (!defined('ALABASI_SYSTEM')) {
    die('Direct access not permitted');
}

/**
 * فئة التكامل مع Manous API
 * Manous API Integration Class
 */
class ManousAPI {
    
    /**
     * رابط API الأساسي
     */
    private $apiUrl = 'https://api.manus.im/v1';
    
    /**
     * مفتاح API
     */
    private $apiKey;
    
    /**
     * نموذج الذكاء الاصطناعي المستخدم
     */
    private $model = 'gpt-4.1-mini';
    
    /**
     * الحد الأقصى للرموز في الاستجابة
     */
    private $maxTokens = 2000;
    
    /**
     * درجة الحرارة للإبداع
     */
    private $temperature = 0.7;
    
    /**
     * Constructor
     */
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?? getenv('MANOUS_API_KEY');
        
        if (empty($this->apiKey)) {
            throw new Exception('Manous API key is required');
        }
    }
    
    /**
     * تحليل البيانات المحاسبية
     * Analyze Accounting Data
     */
    public function analyzeAccountingData($data, $analysisType = 'general') {
        $prompt = $this->buildAnalysisPrompt($data, $analysisType);
        return $this->sendRequest($prompt);
    }
    
    /**
     * اقتراح التقارير المحاسبية
     * Suggest Accounting Reports
     */
    public function suggestReports($accountingData) {
        $prompt = "بناءً على البيانات المحاسبية التالية، اقترح أفضل التقارير المالية التي يمكن إنشاؤها:\n\n";
        $prompt .= json_encode($accountingData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt .= "\n\nيرجى تقديم قائمة بالتقارير المقترحة مع شرح مختصر لكل تقرير وأهميته.";
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * تحليل القيود المحاسبية
     * Analyze Journal Entries
     */
    public function analyzeJournalEntries($entries) {
        $prompt = "قم بتحليل القيود المحاسبية التالية وتقديم ملاحظات حول:\n";
        $prompt .= "1. صحة القيود\n";
        $prompt .= "2. التوازن بين المدين والدائن\n";
        $prompt .= "3. أي أخطاء محتملة\n";
        $prompt .= "4. اقتراحات للتحسين\n\n";
        $prompt .= "القيود المحاسبية:\n";
        $prompt .= json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * توليد تقرير مالي ذكي
     * Generate Smart Financial Report
     */
    public function generateFinancialReport($reportType, $data, $period) {
        $prompt = "أنشئ تقريراً مالياً من نوع '$reportType' للفترة '$period' بناءً على البيانات التالية:\n\n";
        $prompt .= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt .= "\n\nيجب أن يتضمن التقرير:\n";
        $prompt .= "1. ملخص تنفيذي\n";
        $prompt .= "2. تحليل مفصل للأرقام\n";
        $prompt .= "3. مؤشرات الأداء الرئيسية\n";
        $prompt .= "4. توصيات وملاحظات\n";
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * التنبؤ بالتدفقات النقدية
     * Predict Cash Flow
     */
    public function predictCashFlow($historicalData, $months = 3) {
        $prompt = "بناءً على البيانات التاريخية التالية، قم بالتنبؤ بالتدفقات النقدية للأشهر الـ $months القادمة:\n\n";
        $prompt .= json_encode($historicalData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt .= "\n\nيرجى تقديم:\n";
        $prompt .= "1. توقعات التدفقات النقدية الشهرية\n";
        $prompt .= "2. تحليل الاتجاهات\n";
        $prompt .= "3. المخاطر المحتملة\n";
        $prompt .= "4. التوصيات\n";
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * اكتشاف الأنماط الغير عادية
     * Detect Anomalies
     */
    public function detectAnomalies($transactions) {
        $prompt = "قم بتحليل المعاملات التالية واكتشاف أي أنماط غير عادية أو مشبوهة:\n\n";
        $prompt .= json_encode($transactions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt .= "\n\nركز على:\n";
        $prompt .= "1. معاملات غير اعتيادية\n";
        $prompt .= "2. أنماط مشبوهة\n";
        $prompt .= "3. انحرافات عن المعتاد\n";
        $prompt .= "4. توصيات للمراجعة\n";
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * مساعد محاسبي ذكي
     * Smart Accounting Assistant
     */
    public function askAccountingQuestion($question, $context = []) {
        $prompt = "أنت مساعد محاسبي خبير. أجب على السؤال التالي:\n\n";
        $prompt .= "$question\n\n";
        
        if (!empty($context)) {
            $prompt .= "السياق:\n";
            $prompt .= json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        return $this->sendRequest($prompt);
    }
    
    /**
     * بناء prompt للتحليل
     */
    private function buildAnalysisPrompt($data, $analysisType) {
        $prompts = [
            'general' => 'قم بتحليل شامل للبيانات المحاسبية التالية وقدم رؤى وتوصيات',
            'profitability' => 'حلل الربحية بناءً على البيانات المحاسبية التالية',
            'liquidity' => 'حلل السيولة والقدرة على الوفاء بالالتزامات',
            'efficiency' => 'حلل كفاءة العمليات والاستخدام الأمثل للموارد',
            'solvency' => 'حلل الملاءة المالية والاستقرار المالي طويل الأجل'
        ];
        
        $prompt = $prompts[$analysisType] ?? $prompts['general'];
        $prompt .= ":\n\n";
        $prompt .= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return $prompt;
    }
    
    /**
     * إرسال طلب إلى Manous API
     */
    private function sendRequest($prompt, $systemMessage = null) {
        $messages = [];
        
        if ($systemMessage) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemMessage
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => 'أنت مساعد محاسبي خبير متخصص في التحليل المالي والمحاسبة. تقدم إجابات دقيقة ومفصلة باللغة العربية.'
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ];
        
        $ch = curl_init($this->apiUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP $httpCode - $response");
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return [
            'success' => true,
            'content' => $result['choices'][0]['message']['content'] ?? '',
            'usage' => $result['usage'] ?? [],
            'model' => $result['model'] ?? $this->model
        ];
    }
    
    /**
     * تعيين النموذج
     */
    public function setModel($model) {
        $this->model = $model;
        return $this;
    }
    
    /**
     * تعيين الحد الأقصى للرموز
     */
    public function setMaxTokens($tokens) {
        $this->maxTokens = $tokens;
        return $this;
    }
    
    /**
     * تعيين درجة الحرارة
     */
    public function setTemperature($temp) {
        $this->temperature = $temp;
        return $this;
    }
}

/**
 * دالة مساعدة للوصول السريع إلى Manous API
 */
function manous($apiKey = null) {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ManousAPI($apiKey);
    }
    
    return $instance;
}

?>
