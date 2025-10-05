# 🏢 Ubix Central Management Knowledge Base

**Complete Guide for Managing Multi-Tenant Property Management System**

This comprehensive knowledge base provides detailed guidance for administrators managing the Ubix multi-tenant platform, covering tenant management, system administration, billing, and platform operations.

---

## 🚀 Platform Overview

### System Architecture
The Ubix platform operates on a **domain-based multi-tenant architecture** where:
- **Central Domain**: Primary administration portal for platform management
- **Tenant Domains**: Individual property management interfaces (e.g., hotel1.ubix.com)
- **Complete Data Isolation**: Each tenant operates independently with secure data separation
- **Shared Infrastructure**: Common codebase with tenant-specific customizations

### Administrative Roles
- **Super Administrator**: Full platform access and tenant management
- **Platform Manager**: Tenant operations and support management
- **Billing Administrator**: Subscription and payment management
- **Technical Support**: System maintenance and troubleshooting
- **Sales Manager**: New tenant onboarding and account management

---

## 🏢 Tenant Management

### Creating New Tenants

**Step-by-Step Tenant Creation:**
1. **Navigate to Tenants** → **Create New Tenant**
2. **Basic Information Setup:**
   - Tenant name and business information
   - Primary contact details
   - Business type (Hotel, Vacation Rental, etc.)
   - Geographic location and timezone

3. **Domain Configuration:**
   - Choose subdomain (e.g., grandhotel.ubix.com)
   - Configure custom domain (optional)
   - SSL certificate setup (automatic)
   - DNS configuration assistance

4. **Subscription Assignment:**
   - Select appropriate subscription plan
   - Set billing cycle (monthly/yearly)
   - Configure trial period if applicable
   - Set up payment method

5. **Database Initialization:**
   - Tenant database creation (automatic)
   - Default data seeding
   - Permission structure setup
   - Initial user account creation

6. **Onboarding Setup:**
   - Welcome email automation
   - Onboarding checklist creation
   - Training resource assignment
   - Support contact assignment

### Tenant Status Management

**Tenant Lifecycle States:**
- **🟢 Active**: Fully operational with current subscription
- **🟡 Trial**: Free trial period, limited features
- **🟠 Suspended**: Temporary suspension due to payment issues
- **🔴 Inactive**: Deactivated tenant with data retention
- **⚫ Archived**: Permanently closed with data backup

**Status Change Procedures:**

**Trial to Active Conversion:**
1. Verify payment method setup
2. Confirm subscription plan selection
3. Process initial payment
4. Activate full feature set
5. Send activation confirmation
6. Schedule follow-up check-in

**Suspension Process:**
1. Send payment reminder notifications
2. Grace period monitoring (typically 7 days)
3. Feature limitation implementation
4. Suspension notification to tenant
5. Data access restriction (read-only)
6. Reactivation procedures upon payment

**Tenant Deactivation:**
1. Final billing reconciliation
2. Data export preparation
3. Service termination notice
4. Domain cleanup and redirection
5. Archive creation with retention policy
6. Customer exit interview (optional)

### Tenant Configuration Management

**System Settings per Tenant:**
- **Feature Toggles**: Enable/disable specific modules
- **Usage Limits**: Set property, user, and booking limits
- **Custom Branding**: Logo, colors, and tenant-specific styling
- **Integration Settings**: Third-party service configurations
- **Backup Schedules**: Automated backup frequency and retention
- **Support Tier**: Level of support and response times

**Bulk Operations:**
- **Mass Updates**: Apply settings across multiple tenants
- **Feature Rollouts**: Gradual deployment of new features
- **Billing Updates**: Subscription changes across tenant groups
- **Maintenance Windows**: Coordinated system updates
- **Communication Campaigns**: Platform-wide announcements

---

## 💰 Subscription & Billing Management

### Subscription Plans Management

**Current Plan Structure:**

**Starter Plan ($29.99/month):**
- 1 Property maximum
- 2 Users maximum
- Basic reporting features
- Email support
- Standard backup retention

**Professional Plan ($79.99/month):**
- 3 Properties maximum
- 5 Users maximum
- Advanced reporting and analytics
- Priority support
- Extended backup retention
- Multi-property management tools

**Enterprise Plan ($199.99/month):**
- 10 Properties maximum
- 20 Users maximum
- Full analytics suite
- 24/7 dedicated support
- API access and integrations
- Custom feature development options

**Plan Modification Procedures:**
1. **Upgrade Process:**
   - Immediate feature activation
   - Prorated billing calculation
   - Usage limit adjustments
   - Notification to tenant administrators
   - Feature training (if required)

2. **Downgrade Process:**
   - Compliance check (ensure within new limits)
   - Grace period for data cleanup
   - Feature deactivation timeline
   - Billing adjustment processing
   - User notification and assistance

### Payment Processing

**Payment Methods Supported:**
- **Credit/Debit Cards**: Stripe integration with PCI compliance
- **Bank Transfers**: ACH and wire transfer processing
- **Digital Wallets**: PayPal, Apple Pay, Google Pay
- **Enterprise Billing**: Invoice-based billing for large accounts
- **Cryptocurrency**: Bitcoin and Ethereum (enterprise only)

**Billing Cycle Management:**
1. **Monthly Billing:**
   - Automatic charge on subscription anniversary
   - 3-day payment retry schedule
   - Suspension after 7 days of failed payment
   - Reactivation upon successful payment

2. **Annual Billing:**
   - 15% discount applied automatically
   - 30-day advance notice for renewals
   - Payment failure handling with extended grace period
   - Mid-cycle upgrade/downgrade prorating

**Invoicing Process:**
- **Automatic Generation**: 7 days before billing date
- **Manual Invoices**: For custom charges and adjustments
- **Payment Confirmation**: Automated receipt generation
- **Tax Handling**: Automatic tax calculation by region
- **Dispute Resolution**: Chargeback and dispute management

### Revenue Analytics

**Key Metrics Tracking:**
- **Monthly Recurring Revenue (MRR)**: Consistent revenue streams
- **Annual Recurring Revenue (ARR)**: Yearly revenue projections
- **Customer Lifetime Value (CLV)**: Long-term revenue per tenant
- **Churn Rate**: Monthly and annual tenant loss rates
- **Average Revenue Per User (ARPU)**: Revenue efficiency metrics

**Financial Reporting:**
- **Revenue Dashboard**: Real-time revenue tracking
- **Subscription Analytics**: Plan distribution and trends
- **Payment Success Rates**: Processing efficiency metrics
- **Forecast Modeling**: Predictive revenue analysis
- **Tax Reporting**: Automated tax calculation and reporting

---

## 🛠 System Administration

### Database Management

**Central Database Operations:**
- **Tenant Registry**: Master list of all tenants and configurations
- **Subscription Data**: Billing and plan information
- **Usage Metrics**: Platform-wide analytics and monitoring
- **System Logs**: Comprehensive activity logging
- **Backup Management**: Central backup coordination

**Tenant Database Operations:**
- **Database Creation**: Automated provisioning for new tenants
- **Migration Management**: Schema updates across all tenants
- **Performance Monitoring**: Database performance tracking
- **Backup Verification**: Ensuring backup integrity
- **Data Retention**: Compliance with data retention policies

**Database Maintenance Procedures:**
1. **Regular Maintenance:**
   - Weekly performance optimization
   - Index maintenance and cleanup
   - Storage usage monitoring
   - Security patch application
   - Backup verification testing

2. **Migration Deployment:**
   - Staging environment testing
   - Gradual rollout to tenant databases
   - Rollback procedures preparation
   - Performance impact monitoring
   - Success verification and reporting

### Server Infrastructure

**Server Environment Management:**
- **Load Balancing**: Traffic distribution across servers
- **Auto-scaling**: Automatic resource adjustment
- **CDN Management**: Content delivery optimization
- **SSL Certificate Management**: Automated certificate renewal
- **Security Monitoring**: Intrusion detection and prevention

**Performance Monitoring:**
- **Server Metrics**: CPU, memory, disk usage tracking
- **Application Performance**: Response time monitoring
- **Database Performance**: Query optimization and monitoring
- **Network Performance**: Bandwidth and latency tracking
- **User Experience Metrics**: Page load times and error rates

**Maintenance Procedures:**
1. **Scheduled Maintenance:**
   - Monthly server updates and patches
   - Quarterly performance optimization
   - Semi-annual security audits
   - Annual infrastructure reviews
   - Emergency maintenance protocols

2. **Capacity Planning:**
   - Growth projection analysis
   - Resource utilization forecasting
   - Infrastructure scaling recommendations
   - Budget planning for upgrades
   - Disaster recovery planning

### Security Management

**Access Control:**
- **Multi-Factor Authentication**: Required for all admin accounts
- **Role-Based Permissions**: Granular access control
- **Session Management**: Secure session handling
- **API Security**: Token-based authentication and rate limiting
- **Audit Logging**: Comprehensive activity tracking

**Data Protection:**
- **Encryption**: Data at rest and in transit
- **Backup Security**: Encrypted backup storage
- **Privacy Compliance**: GDPR, CCPA, and regional compliance
- **Data Anonymization**: Personal data protection
- **Retention Policies**: Automated data lifecycle management

**Security Monitoring:**
- **Threat Detection**: Real-time security monitoring
- **Vulnerability Scanning**: Regular security assessments
- **Incident Response**: Security incident management
- **Penetration Testing**: Annual security testing
- **Compliance Auditing**: Regular compliance verification

---

## 📞 Customer Support Management

### Support Ticket System

**Ticket Categories:**
- **🔴 Critical**: System down, data loss, security issues
- **🟠 High**: Feature not working, billing problems
- **🟡 Medium**: Minor bugs, feature requests
- **🟢 Low**: General questions, training requests

**Support Response SLAs:**
- **Critical**: 1 hour response, 4 hour resolution
- **High**: 4 hour response, 24 hour resolution
- **Medium**: 24 hour response, 72 hour resolution
- **Low**: 72 hour response, 1 week resolution

**Escalation Procedures:**
1. **Level 1**: Front-line support (general issues)
2. **Level 2**: Technical specialists (complex technical issues)
3. **Level 3**: Senior engineers (system-level problems)
4. **Management**: Critical issues requiring executive attention

### Knowledge Base Management

**Content Management:**
- **Article Creation**: Technical documentation and user guides
- **Video Tutorials**: Screen recordings for complex procedures
- **FAQ Maintenance**: Regular update of common questions
- **Search Optimization**: Content tagging and categorization
- **Analytics Tracking**: Usage metrics and effectiveness

**Support Documentation:**
- **Internal Procedures**: Support staff training materials
- **Troubleshooting Guides**: Common issue resolution steps
- **Escalation Procedures**: When and how to escalate issues
- **Customer Communication**: Templates and best practices
- **System Knowledge**: Technical documentation for platform

### Training & Onboarding

**New Tenant Onboarding:**
1. **Welcome Package**: Introduction materials and quick start guide
2. **Setup Assistance**: Guided property and room configuration
3. **Training Sessions**: Live demonstrations of key features
4. **Check-in Calls**: Progress monitoring and assistance
5. **Success Metrics**: Tracking onboarding completion and satisfaction

**Ongoing Training Programs:**
- **Feature Updates**: Training on new features and improvements
- **Best Practices**: Industry-specific guidance and tips
- **Advanced Features**: Deep-dive training for power users
- **Certification Programs**: Optional certification for proficiency
- **User Communities**: Forums and user groups for peer learning

---

## 📊 Analytics & Monitoring

### Platform Analytics

**Usage Metrics:**
- **Daily Active Tenants**: Engagement tracking
- **Feature Adoption**: Usage patterns across features
- **Performance Metrics**: System response times and reliability
- **Growth Metrics**: New tenant acquisition and retention
- **Revenue Metrics**: Financial performance tracking

**Tenant Analytics:**
- **Usage Patterns**: How tenants use the platform
- **Feature Utilization**: Most and least used features
- **Support Needs**: Common issues and requests
- **Success Metrics**: Tenant performance and satisfaction
- **Churn Analysis**: Reasons for tenant departures

**Business Intelligence:**
- **Market Analysis**: Industry trends and opportunities
- **Competitive Analysis**: Feature comparison and positioning
- **Product Development**: Data-driven feature prioritization
- **Sales Intelligence**: Lead qualification and conversion tracking
- **Customer Success**: Satisfaction and retention metrics

### System Monitoring

**Real-time Monitoring:**
- **System Health**: Server and database status
- **Application Performance**: Response times and error rates
- **User Activity**: Real-time usage monitoring
- **Security Events**: Threat detection and response
- **Backup Status**: Backup completion and verification

**Alerting Systems:**
- **Performance Alerts**: System performance degradation
- **Security Alerts**: Suspicious activity detection
- **Billing Alerts**: Payment failures and subscription issues
- **Capacity Alerts**: Resource utilization thresholds
- **Error Alerts**: Application errors and exceptions

**Reporting Dashboards:**
- **Executive Dashboard**: High-level business metrics
- **Operations Dashboard**: System health and performance
- **Financial Dashboard**: Revenue and billing metrics
- **Support Dashboard**: Ticket volume and resolution metrics
- **Security Dashboard**: Security events and compliance status

---

## 🔄 Platform Operations

### Feature Development & Deployment

**Development Lifecycle:**
1. **Feature Planning**: Requirements gathering and prioritization
2. **Development**: Code development and testing
3. **Quality Assurance**: Comprehensive testing procedures
4. **Staging Deployment**: Testing in production-like environment
5. **Production Deployment**: Gradual rollout to all tenants
6. **Post-deployment Monitoring**: Performance and adoption tracking

**Feature Flagging:**
- **Gradual Rollouts**: Phased feature deployment
- **A/B Testing**: Feature effectiveness testing
- **Emergency Rollbacks**: Quick feature disabling capabilities
- **Tenant-specific Features**: Custom feature enablement
- **Beta Testing**: Early access for select tenants

**Quality Assurance:**
- **Automated Testing**: Continuous integration testing
- **Manual Testing**: User acceptance testing
- **Performance Testing**: Load and stress testing
- **Security Testing**: Vulnerability assessment
- **Compliance Testing**: Regulatory compliance verification

### Maintenance & Updates

**Scheduled Maintenance:**
- **Weekly Maintenance**: Minor updates and optimizations
- **Monthly Maintenance**: Security patches and performance improvements
- **Quarterly Maintenance**: Major updates and feature releases
- **Annual Maintenance**: Infrastructure upgrades and reviews

**Emergency Maintenance:**
- **Security Issues**: Immediate response to security threats
- **Critical Bugs**: Urgent fixes for system-breaking issues
- **Performance Issues**: Resolution of severe performance problems
- **Data Issues**: Recovery from data corruption or loss
- **Infrastructure Failures**: Response to hardware or service failures

**Communication Procedures:**
- **Advance Notices**: 72-hour notice for scheduled maintenance
- **Status Pages**: Real-time system status updates
- **Email Notifications**: Direct communication to tenant administrators
- **In-app Notifications**: System-wide announcements
- **Post-maintenance Reports**: Summary of completed work

---

## 📋 Compliance & Legal

### Data Protection Compliance

**GDPR Compliance:**
- **Data Processing Records**: Detailed processing documentation
- **Consent Management**: User consent tracking and management
- **Right to be Forgotten**: Data deletion procedures
- **Data Portability**: User data export capabilities
- **Breach Notification**: Incident reporting procedures

**Regional Compliance:**
- **CCPA (California)**: California privacy law compliance
- **PIPEDA (Canada)**: Canadian privacy law compliance
- **LGPD (Brazil)**: Brazilian data protection compliance
- **Regional Variations**: Country-specific requirement handling

**Security Compliance:**
- **SOC 2 Type II**: Security controls certification
- **ISO 27001**: Information security management
- **PCI DSS**: Payment card industry compliance
- **HIPAA (if applicable)**: Healthcare data protection
- **Industry Standards**: Hospitality-specific requirements

### Legal Documentation

**Terms of Service:**
- **Platform Terms**: Overall platform usage terms
- **Tenant Agreements**: Specific tenant service agreements
- **Privacy Policies**: Data handling and privacy practices
- **Service Level Agreements**: Performance and availability commitments
- **Acceptable Use Policies**: Platform usage guidelines

**Compliance Documentation:**
- **Audit Reports**: Regular compliance audits
- **Certification Documents**: Industry certifications
- **Legal Reviews**: Regular legal compliance reviews
- **Risk Assessments**: Security and legal risk evaluations
- **Policy Updates**: Regular policy review and updates

---

## 🚨 Emergency Procedures

### Incident Response

**Incident Classification:**
- **P1 (Critical)**: Complete system outage affecting all tenants
- **P2 (High)**: Partial outage affecting multiple tenants
- **P3 (Medium)**: Single tenant issues or performance degradation
- **P4 (Low)**: Minor issues with workarounds available

**Response Procedures:**
1. **Detection**: Automated monitoring and manual reporting
2. **Assessment**: Impact analysis and severity classification
3. **Communication**: Stakeholder notification and status updates
4. **Resolution**: Technical response and fix implementation
5. **Recovery**: Service restoration and verification
6. **Post-mortem**: Incident analysis and improvement planning

**Communication Plans:**
- **Internal Communication**: Staff notification and coordination
- **External Communication**: Customer notification and updates
- **Stakeholder Updates**: Regular progress reporting
- **Media Relations**: Public relations and press communication
- **Regulatory Notification**: Compliance-required notifications

### Disaster Recovery

**Backup Systems:**
- **Real-time Replication**: Continuous data synchronization
- **Geographic Distribution**: Multi-region backup storage
- **Recovery Point Objective (RPO)**: Maximum 15 minutes data loss
- **Recovery Time Objective (RTO)**: Maximum 4 hours downtime
- **Testing Schedule**: Monthly disaster recovery testing

**Business Continuity:**
- **Alternative Infrastructure**: Backup data centers and services
- **Staff Continuity**: Remote work capabilities and procedures
- **Communication Systems**: Backup communication channels
- **Vendor Relationships**: Alternative service providers
- **Financial Continuity**: Insurance and financial protections

---

## 📈 Growth & Scaling

### Capacity Planning

**Growth Projections:**
- **Tenant Growth**: New customer acquisition forecasts
- **Usage Growth**: Existing customer expansion patterns
- **Geographic Expansion**: New market entry planning
- **Feature Development**: Platform capability expansion
- **Technology Evolution**: Infrastructure advancement planning

**Resource Scaling:**
- **Server Capacity**: Computing resource expansion
- **Database Scaling**: Database performance and capacity
- **Network Capacity**: Bandwidth and connectivity scaling
- **Storage Expansion**: Data storage growth planning
- **Support Scaling**: Customer support team growth

**Performance Optimization:**
- **Code Optimization**: Application performance improvements
- **Database Tuning**: Query and index optimization
- **Caching Strategies**: Performance enhancement through caching
- **CDN Optimization**: Content delivery optimization
- **Load Balancing**: Traffic distribution improvements

### Market Expansion

**New Market Research:**
- **Industry Analysis**: Hospitality market research
- **Competitive Analysis**: Market positioning studies
- **Regulatory Research**: Compliance requirement analysis
- **Cultural Considerations**: Regional customization needs
- **Partnership Opportunities**: Strategic alliance identification

**Localization Requirements:**
- **Language Support**: Multi-language platform development
- **Currency Handling**: Multi-currency transaction support
- **Regional Features**: Country-specific feature development
- **Legal Compliance**: Regional regulatory compliance
- **Cultural Adaptation**: User experience localization

---

## 🔧 Troubleshooting Guide

### Common Issues

**Tenant Creation Problems:**
- **Domain Conflicts**: Subdomain already exists or conflicts
- **Database Errors**: Database creation failures
- **Email Delivery**: Welcome email delivery problems
- **Payment Setup**: Initial payment processing issues
- **DNS Configuration**: Custom domain setup problems

**Performance Issues:**
- **Slow Response Times**: Database query optimization needs
- **High Server Load**: Resource capacity problems
- **Memory Issues**: Application memory leaks or high usage
- **Database Locks**: Query blocking and deadlock issues
- **Network Latency**: Connection speed and routing problems

**Billing Problems:**
- **Payment Failures**: Credit card declines and expired cards
- **Subscription Errors**: Plan change processing problems
- **Refund Processing**: Refund request handling issues
- **Tax Calculation**: Incorrect tax amount calculations
- **Invoice Generation**: Automated invoice creation problems

### Diagnostic Procedures

**System Health Checks:**
1. **Server Status**: Verify all servers are online and responsive
2. **Database Connectivity**: Check database connections and performance
3. **Application Status**: Verify application services are running
4. **External Services**: Check third-party service availability
5. **Network Connectivity**: Verify network routing and performance

**Performance Analysis:**
1. **Response Time Analysis**: Identify slow-performing requests
2. **Resource Utilization**: Check CPU, memory, and disk usage
3. **Database Performance**: Analyze query performance and blocking
4. **Cache Effectiveness**: Verify caching system performance
5. **User Experience Metrics**: Monitor real user experience data

**Error Investigation:**
1. **Log Analysis**: Review application and system logs
2. **Error Tracking**: Use error monitoring tools for investigation
3. **User Reproduction**: Reproduce issues in controlled environment
4. **Data Validation**: Verify data integrity and consistency
5. **Third-party Dependencies**: Check external service dependencies

---

## 📞 Support Contacts & Resources

### Internal Team Contacts

**Platform Management:**
- **Platform Director**: Strategic oversight and major decisions
- **Operations Manager**: Daily operations and process management
- **Technical Lead**: System architecture and development oversight
- **Security Officer**: Security compliance and incident response
- **Customer Success Manager**: Tenant relationship management

**Technical Support:**
- **Senior Engineers**: Level 3 technical support and escalation
- **Support Specialists**: Level 1 and 2 customer support
- **Database Administrator**: Database management and optimization
- **DevOps Engineer**: Infrastructure and deployment management
- **Quality Assurance Lead**: Testing and quality oversight

**Business Operations:**
- **Billing Administrator**: Subscription and payment management
- **Sales Manager**: New tenant acquisition and growth
- **Marketing Manager**: Platform promotion and communications
- **Legal Counsel**: Compliance and legal matters
- **Finance Manager**: Financial planning and analysis

### External Resources

**Technology Partners:**
- **Cloud Provider (AWS/Azure)**: Infrastructure support and services
- **Payment Processor (Stripe)**: Payment processing support
- **Monitoring Services**: System monitoring and alerting
- **Security Services**: Security scanning and compliance
- **Backup Services**: Data backup and recovery services

**Professional Services:**
- **Legal Counsel**: External legal support and compliance
- **Accounting Firm**: Financial auditing and tax preparation
- **Security Consultants**: Penetration testing and security audits
- **Compliance Advisors**: Regulatory compliance guidance
- **Industry Consultants**: Hospitality industry expertise

### Emergency Contacts

**Critical Incidents:**
- **Emergency Hotline**: 24/7 critical incident response
- **Security Incidents**: Immediate security threat response
- **Legal Emergencies**: Urgent legal matter support
- **Executive Escalation**: C-level emergency contacts
- **Vendor Emergency**: Critical vendor issue contacts

---

## 🆕 Platform Updates & Roadmap

### Recent Platform Updates

**Version 2.1.0 (October 2025):**
- Enhanced tenant management dashboard with real-time metrics
- Improved billing system with automatic retry logic
- Advanced analytics with custom reporting capabilities
- Security enhancements with additional compliance features
- Performance optimizations reducing average response time by 25%

**Version 2.0.0 (September 2025):**
- Complete UI/UX redesign for better user experience
- Multi-language support for international tenants
- Advanced role-based access control system
- Automated backup and disaster recovery improvements
- Integration with major payment gateways worldwide

### Upcoming Features (Q4 2025)

**Platform Enhancements:**
- **AI-Powered Analytics**: Machine learning insights for tenant success
- **Advanced Automation**: Workflow automation for common tasks
- **Mobile App**: Native mobile app for platform management
- **API v2**: Enhanced API with better performance and features
- **White-label Options**: Complete branding customization capabilities

**Tenant Experience Improvements:**
- **Channel Manager Integration**: Direct OTA connectivity
- **Guest Communication Suite**: Automated guest messaging
- **Revenue Management Tools**: Dynamic pricing and optimization
- **Mobile Guest Services**: Native mobile app for guests
- **IoT Integration**: Smart room and building automation

### Long-term Roadmap (2026)

**Strategic Initiatives:**
- **Global Expansion**: Support for 50+ countries and currencies
- **Industry Specialization**: Vertical-specific feature sets
- **Marketplace Platform**: Third-party app and integration marketplace
- **Blockchain Integration**: Decentralized identity and payments
- **Sustainability Features**: Environmental impact tracking and reporting

---

*This knowledge base is continuously updated to reflect the latest platform capabilities, procedures, and best practices. For immediate assistance with critical issues, contact the emergency support hotline. For general questions or suggestions for knowledge base improvements, contact the platform management team.*

**Last Updated: October 5, 2025**  
**Document Version: 2.1**  
**Next Review Date: November 5, 2025**